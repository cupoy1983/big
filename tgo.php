<?php
$url = base64_decode($_REQUEST['url']);
if(empty($url))
	exit;

$fuid = 0;
$sid = 0;
$gid = 0;
$kid = '';
if(isset($_REQUEST['args']))
{
	$args = unserialize(base64_decode($_REQUEST['args']));
	if(!$args)
		exit;
	$fuid = (int)$args['uid'];
	$sid = (int)$args['sid'];
	$gid = (int)$args['gid'];
	$kid = $args['kid'];
}

if(strpos($url,'s.click.taobao.com'))
{
	if($fuid > 0)
	{
		include dirname(__FILE__).'/public/data/caches/system/setting.cache.php';
		$is_open_commission = (int)$data['setting']['is_open_commission'];
		if($is_open_commission == 1 && $fuid > 0 && $sid > 0 && $gid > 0 && !empty($kid))
		{
			define('TGO_URL',$url);
			define('TGO_FUID',$fuid);
			define('TGO_SID',$sid);
			define('TGO_GID',$gid);
			define('TGO_KEYID',$kid);

			define('MODULE_NAME','Tgo');
			define('ACTION_NAME','index');
			require dirname(__FILE__).'/core/fanwe.php';
			$fanwe = &FanweService::instance();
			$fanwe->cache_list = array('user_group');
			$fanwe->initialize();
			$url = TGO_URL;
			$uid = $_FANWE['uid'];

			if($uid > 0 && $uid == TGO_FUID)
				$fuser_group = $_FANWE['user_group'];
			else
			{
				$fuser_group_id = FDB::resultFirst('SELECT gid FROM '.FDB::table('user').' WHERE uid = '.TGO_FUID);
				$fuser_group = $_FANWE['cache']['user_group'][$fuser_group_id];
			}

			$goods = FS('Goods')->getById(TGO_GID);
			if(!$goods)
				exit;
			
			$id = 0;
			$order = array();
			$order['create_time'] = TIME_UTC;
			$order['share_id'] = TGO_SID;
			$order['goods_id'] = TGO_GID;
			$order['goods_name'] = '<a href="'.$goods['url'].'" target="_blank">'.addslashes($goods['name']).'</a>';
			$order['keyid'] = addslashes(TGO_KEYID);
                        
                        if(checkUserTgo(TGO_FUID,$order)){
                            $is_special = (int)$fuser_group['is_special'];
                            if($is_special == 0)
                            {
                                    if($uid != TGO_FUID)
                                    {
                                            $rate = (float)$fuser_group['goods_commission_score_rate'];
                                            $type = 0;
                                    }
                                    else
                                    {
                                            $rate = (float)$_FANWE['user_group']['goods_buy_score_rate'];
                                            $type = 1;
                                    }

                                    if($rate > 0)
                                    {
                                            $id = FDB::insert('goods_order_index',array('id'=>'NULL','create_day'=>getTodayTime()),true);
                                            $order['order_id'] = $id;
                                            $order['commission_rate'] = $rate;
                                            $order['uid'] = TGO_FUID;
                                            $order['rel_uid'] = $uid;
                                            $order['type'] = $type;
                                            FDB::insert('goods_order',$order);
                                    }
                            }

                            $is_special = (int)$_FANWE['user_group']['is_special'];
                            $rate = (float)$_FANWE['user_group']['goods_buy_score_rate'];
                            if($uid > 0 && $is_special == 0 && $rate > 0 && ($id == 0 || $uid != TGO_FUID))
                            {
                                    if($id == 0)
                                            $id = FDB::insert('goods_order_index',array('id'=>'NULL','create_day'=>getTodayTime()),true);

                                    $order['order_id'] = $id;
                                    $order['commission_rate'] = $rate;
                                    $order['uid'] = $uid;
                                    $order['rel_uid'] = TGO_FUID;
                                    $order['type'] = '1';
                                    FDB::insert('goods_order',$order);
                            }

                            if($id > 0)
                                    $url .= '&unid=o'.$id;
                        }
			
		}
	}
}
/*elseif(strpos($url,'g.yiqifa.com'))
{
	if($fuid > 0)
	{
		include dirname(__FILE__).'/public/data/caches/system/setting.cache.php';
		$is_open_commission = (int)$data['setting']['is_open_commission'];
		if($is_open_commission == 1 && $fuid > 0 && $sid > 0 && $gid > 0 && !empty($kid))
		{
			define('TGO_URL',$url);
			define('TGO_FUID',$fuid);
			define('TGO_SID',$sid);
			define('TGO_GID',$gid);
			define('TGO_KEYID',$kid);

			define('MODULE_NAME','Tgo');
			define('ACTION_NAME','index');
			require dirname(__FILE__).'/core/fanwe.php';
			$fanwe = &FanweService::instance();
			$fanwe->cache_list = array('user_group');
			$fanwe->initialize();
			$url = TGO_URL;
			$uid = $_FANWE['uid'];

			if($uid > 0 && $uid == TGO_FUID)
				$fuser_group = $_FANWE['user_group'];
			else
			{
				$fuser_group_id = FDB::resultFirst('SELECT gid FROM '.FDB::table('user').' WHERE uid = '.TGO_FUID);
				$fuser_group = $_FANWE['cache']['user_group'][$fuser_group_id];
			}
			
			$id = 0;
			$order = array();
			$order['create_time'] = TIME_UTC;
			$order['share_id'] = TGO_SID;
			$order['goods_id'] = TGO_GID;
			$order['keyid'] = addslashes(TGO_KEYID);

			$is_special = (int)$fuser_group['is_special'];
			$rate = (float)$fuser_group['commission_rate'];
			if($is_special == 0 && $rate > 0)
			{
				$id = FDB::insert('goods_order_index',array('id'=>'NULL','create_day'=>getTodayTime()),true);
				$order['order_id'] = $id;
				$order['commission_rate'] = $rate;
				$order['uid'] = TGO_FUID;
				$order['rel_uid'] = $uid;
				$order['type'] = '0';
				FDB::insert('goods_order',$order);
			}
			
			$is_special = (int)$_FANWE['user_group']['is_special'];
			$rate = (float)$_FANWE['user_group']['buy_rate'];
			if($uid > 0 && $is_special == 0 && $rate > 0 && ($id == 0 || $uid != TGO_FUID))
			{
				if($id == 0)
					$id = FDB::insert('goods_order_index',array('id'=>'NULL','create_day'=>getTodayTime()),true);

				$order['order_id'] = $id;
				$order['commission_rate'] = $rate;
				$order['uid'] = $uid;
				$order['rel_uid'] = TGO_FUID;
				$order['type'] = '1';
				FDB::insert('goods_order',$order);
			}

			if($id > 0)
				$url = str_replace('&e=default&','&e=o'.$id.'&',$url);
		}
	}
}*/


/**
 * 用户是否跳转过
 * 没个链接限制跳转记录只允许 5 次，每次间隔时间必须超过 30秒
 */
function checkUserTgo($uid,$order){
    $sql = ' SELECT *,COUNT(keyid) AS count FROM ( SELECT * FROM '.FDB::table('goods_order').' where uid = '.$uid.' AND goods_id ='.$order['goods_id'] .'  AND keyid=\''.$order['keyid'].'\' ORDER BY order_id DESC) as temp_goods_order  GROUP BY keyid';
    $data = FDB::fetchFirst($sql);
    $is_data = true;
    $msg_error = '';
    if($data){
        //判断时间必须超过30秒
        $tgo_time = intval($order['create_time'])-intval($data['create_time']);

        if($tgo_time<=30){
            $is_data = false;
            $msg_error = '未超过30秒';
        }
        if($data['count']>=5){
            $is_data = false;
            $msg_error = '同一商品不得超过5次';
        }
    }
    $message = $msg_error;
    return $is_data;
    
}
header('Location: '.$url);
?>