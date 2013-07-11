<?php
require ROOT_PATH . 'core/fanwe.php';
$fanwe = &FanweService::instance();
$fanwe->is_session = false;
$fanwe->is_user = false;
$fanwe->is_cron = false;
$fanwe->is_misc = false;
$fanwe->cache_list = array();
$fanwe->initialize();

$_FANWE['request'] = unserialize(REQUEST_ARGS);
setTimeLimit(0);
$share_ids = array();
$res = FDB::query('SELECT * FROM ' . FDB::table('delist_share') . ' ORDER BY share_id ASC LIMIT 0,10');
while ($data = FDB::fetch($res)) {
    $share_ids[] = $data['share_id'];
}

if (count($share_ids) > 0) {
    FDB::query('DELETE FROM ' . FDB::table('delist_share') . ' WHERE share_id IN (' . implode(',', $share_ids) . ')');
    $list = FDB::fetchAll('SELECT share_id,rec_id,type FROM ' . FDB::table('share') . ' WHERE share_id IN (' . implode(',', $share_ids) . ')');
    foreach ($list as $item) {
        $share_id = (int) $item['share_id'];
        switch ($item['type']) {
            case 'bar':
                FS("Topic")->deleteTopic($item['rec_id']);
                break;
            case 'ershou':
                FS("Second")->deleteGoods($item['rec_id']);
                break;
            case 'album':
                FS('Album')->deleteAlbum($item['rec_id']);
                break;
            case 'album_item':
                FS('Album')->deleteAlbumItem($share_id);
                break;
            case 'activity':
                FS('Activity')->delete($item['rec_id']);
                break;
            case 'activity_post':
                FS('Activity')->deletePost($share_id);
                break;
            case 'vote':
                FS('Vote')->delete($item['rec_id']);
                break;
            case 'vote_post':
                FS('Vote')->deletePost($share_id);
                break;
            default:
                FS("Share")->deleteShare($share_id);
                break;
        }
    }
    $args = array('m' => 'goods', 'a' => 'delist');
    FS("Cron")->createRequest($args);
} else {
    $goods_ids = array();
    $img_ids = array();

    $res = FDB::query('SELECT id,img_id,keyid FROM ' . FDB::table('goods') . ' WHERE delist_time > 0 && delist_time < ' . TIME_UTC . ' ORDER BY id ASC LIMIT 0,10');

    //遍历所有
    while ($data = FDB::fetch($res)) {
        if (strpos($data['keyid'], 'taobao_') !== false) {
            $taobao_data[$data['keyid']] = substr($data['keyid'], 7);
        }
        $data_all[$data['keyid']] = $data;
    }
    /**
     * 淘宝商品检查是否重新上架
     */
    $num_iids = implode(',', $taobao_data);
    
    //查询淘宝商品是否重新上架
    if (count($taobao_data) > 0) {
        //引入淘宝API 接口文件
        require_once FANWE_ROOT . "sdks/taobao/TopClient.php";
        require_once FANWE_ROOT . 'sdks/taobao/request/TaobaokeItemsDetailGetRequest.php';
        //配置变量
        Cache::getInstance()->loadCache('business');
        //客户端请求对象
        $client = new TopClient;
        $client->appkey = trim($_FANWE['cache']['business']['taobao']['app_key']);
        $client->secretKey = trim($_FANWE['cache']['business']['taobao']['app_secret']);

        $req = new TaobaokeItemsDetailGetRequest;
        //查询商品编号
        $num_iids = implode(',', $taobao_data);
        $req->setFields("num_iid,nick,title,price,list_time,delist_time");  //list_time 上架时间 | delist_time 下架时间
        $req->setNumIids($num_iids);

        $resp = $client->execute($req);
        
        if(isset($resp->taobaoke_item_details) && isset($resp->taobaoke_item_details->taobaoke_item_detail)){
            $list = (array)$resp->taobaoke_item_details;
            $list = $list['taobaoke_item_detail'];
            foreach($list as $item){
                  $arr_item = (array)$item->item;
                  if(strtotime($arr_item['delist_time'])>  time()){
                      $re_list['taobao_'.$arr_item['num_iid']]=strtotime($arr_item['delist_time']);
                  }
            }
            
        }
    }

    //重新上架的商品进行删除,同时更新上架时间
    foreach ($data_all as $key => $value) {
        if (array_key_exists($key, $re_list)) {
            //更新下架时间
            FDB::update('goods',array('delist_time'=>$re_list[$key]),' keyid=\''.$key.'\' ');
            continue;
        }
        $n[] = $key;
        $goods_ids[] = $value['id'];
        if ($value['img_id'] > 0)
            $img_ids[] = $value['img_id'];
    }

    if (count($goods_ids) > 0) {
        FDB::query('DELETE FROM ' . FDB::table('goods') . ' WHERE id IN (' . implode(',', $goods_ids) . ')');
        FDB::query('DELETE FROM ' . FDB::table('goods_comment') . ' WHERE goods_id IN (' . implode(',', $goods_ids) . ')');
        FDB::query('DELETE FROM ' . FDB::table('goods_check') . ' WHERE id IN (' . implode(',', $goods_ids) . ')');
        FDB::query('DELETE FROM ' . FDB::table('goods_match') . ' WHERE id IN (' . implode(',', $goods_ids) . ')');
        //FDB::query('DELETE FROM '.FDB::table('goods_order').' WHERE goods_id IN ('.implode(',',$goods_ids).')');

        FDB::query('INSERT INTO ' . FDB::table('delist_share') . '(share_id) SELECT DISTINCT share_id FROM ' . FDB::table('share_goods') . ' WHERE goods_id IN (' . implode(',', $goods_ids) . ')');

        FS('Image')->deleteImages($img_ids);

        $args = array('m' => 'goods', 'a' => 'delist');
        FS("Cron")->createRequest($args);
    }
    exit;
}
?>
