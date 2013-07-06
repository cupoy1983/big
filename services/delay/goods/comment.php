<?php
//define('ROOT_PATH', str_replace('services/delay/goods/comment.php', '', str_replace('\\', '/', __FILE__)));
require ROOT_PATH.'core/fanwe.php';
$fanwe = &FanweService::instance();
$fanwe->is_session = false;
$fanwe->is_user = false;
$fanwe->is_cron = false;
$fanwe->is_misc = false;
$fanwe->cache_list = array();
$fanwe->initialize();

$_FANWE['request'] = unserialize(REQUEST_ARGS);
$shareId = (int)$_FANWE['request']['id'];
$shareGoods = FS('Goods')->getShareGoodsByShareId($shareId);
$goodsId = $shareGoods['goods_id'];
//modified by frankie 随机获取评论人
$commander = FDB::fetchAll("select uid from ".FDB::table("user")." where gid =8 ORDER BY rand() LIMIT ". 50);
$goods = FS('Goods')->getById($goodsId);

if(empty($goods)){
	exit;
}

setTimeLimit(120);
$today_time = getTodayTime();
if($goods['comment_collect_time'] < $today_time){
	$sql = 'UPDATE '.FDB::table('goods').' SET comment_collect_time = '.$today_time.' WHERE id = '.$goodsId;
	FDB::query($sql);
	if(FDB::affectedRows() > 0){
		$cache_path = PUBLIC_ROOT.'data/caches/custom/share/'.getDirsById($shareId).'/collect/comment.php';
		$phth = dirname($cache_path);
		makeDir($phth);
		$temp_content = '';
		
		if($goods['type'] == 'taobao'){
			$url = $goods['url'];
			$content = getUrlContent($url,true);
			preg_match("/\"valReviewsApi\":\"(.+?)\"/",$content,$comment_url);
			if(!$comment_url)
				preg_match("/\"apiTmallReview\":\"(.+?)\"/",$content,$comment_url);

			if($comment_url){
				$comment_url = stripslashes(trim($comment_url[1]));
				$comments = gbToUTF8(getUrlContent($comment_url));
				
				$pos = strpos($comments,'{');
				$comments = substr($comments,$pos);
				$comments = json_decode($comments,true);

				foreach($comments['rateListInfo']['rateList'] as $k=>$rate){
					$item = array();
					$item['share_id'] = $shareId;
					$item['out_id'] = $rate['id'];
					$item['uid'] = $commander[$k]['uid'];
					$item['content'] = preg_replace("/[\r\n]/",'',$rate['rateContent']);
					$item['content'] = FS('Goods')->generateGoodsName($item['content']);
					$item['create_time'] = str2Time(str_replace('.','-',$rate['rateDate']));
					if(!checkContent($item['content'])){
						continue;
					}
					if(!empty($temp_content)){
						$temp_content .= "\n".serialize($item);
					}else{
						$temp_content .= serialize($item);
					}
				}
			}else{
				$content=preg_replace("/[\r\n]/",'',$content);
				preg_match("/TShop\.Setup\(.*?\"api\":(.*?)\"tradeType\":/",$content,$taobao_json);
				if($taobao_json){
					$taobao_json = '{api:'.substr(trim($taobao_json[1]),0,-1).'}';
					require_once fimport('class/sjson');
					$json = new Services_JSON();
					$taobao_json = $json->decode($taobao_json);
					if($taobao_json && isset($taobao_json->api) && $taobao_json->itemDO && $taobao_json->url){
						$api_url = $taobao_json->api->fetchDcUrl;
						$api_url = parse_url($api_url);
						parse_str($api_url['query'],$api_params);
						$sellerId = $api_params['sellerId'];
						$itemId = $taobao_json->itemDO->itemId;
						$comment_url = $taobao_json->url->rate."/list_detail_rate.htm?callback=jsonp".TIME_UTC."&itemId=".$itemId."&spuId=&sellerId=".$sellerId."&order=0&forShop=1&append=0&currentPage=1&ismore=1";

						$comments = trim(gbToUTF8(getUrlContent($comment_url)));
						$pos = strpos($comments,'{');
						$comments = substr($comments,$pos,-1);
						$comments = json_decode($comments,true);
						
						foreach($comments['rateDetail']['rateList'] as $k=>$rate){
							$item = array();
							$item['share_id'] = $shareId;
							$item['out_id'] = $rate['id'];
							$item['uid'] = $commander[$k]['uid'];
							$item['content'] = preg_replace("/[\r\n]/",'',$rate['rateContent']);
							$item['content'] = FS('Goods')->generateGoodsName($item['content']);
							$item['create_time'] = str2Time(str_replace('.','-',$rate['rateDate']));
							if(!checkContent($item['content'])){
								continue;
							}
							if(!empty($temp_content)){
								$temp_content .= "\n".serialize($item);
							}else{
								$temp_content .= serialize($item);
							}
						}
					}
				}
			}

		}
//      modified by frankie
//
// 		elseif($goods['type'] == 'paipai')
// 		{
// 			$key = str_replace('paipai_','',$goods['keyid']);
// 			$comment_url = 'http://shop1.paipai.com/cgi-bin/creditinfo/CmdyEval?sCmdyId='.$key.'&nCurPage=1&nTotal=200&resettime=1';
// 			$comments = gbToUTF8(getUrlContent($comment_url));
// 			$pos = strpos($comments,'{');
// 			$end = strpos($comments,';try{');
// 			$comments = substr($comments,$pos,$end - $pos);
// 			require_once fimport('class/sjson');
// 			$json = new Services_JSON();
// 			$comments = $json->decode($comments);
// 			if(count($comments->evalList) == 0)
// 				exit;
			
// 			foreach($comments->evalList as $rate)
// 			{
// 				if($rate)
// 				{
// 					$rate = (array)$rate;
// 					$item = array();
// 					$item['content'] = preg_replace("/[\r\n]/",'',$rate['peerEvalContent']);
// 					$item['content'] = FS('Goods')->generateGoodsName($item['content']);
// 					$item['create_time'] = str2Time($rate['peerTime']);
// 					if(!empty($temp_content))
// 						$temp_content .= "\n".serialize($item);
// 					else
// 						$temp_content .= serialize($item);
// 				}
// 			}
// 		}
		if(!empty($temp_content)){
			writeFile($cache_path,$temp_content);
			sleep(1);
			$args = array('m'=>'goods','a'=>'comment','id'=>$shareId,'comment_url'=>$comment_url);
			FS('Delay')->create($args,false);
		}
	}
}else{
	insertGoodsComment($shareId, $shareGoods['uid']);
}

function insertGoodsComment($shareId, $uid){
	$cache_path = PUBLIC_ROOT.'data/caches/custom/share/'.getDirsById($shareId).'/collect/comment.php';
	$list = @file_get_contents($cache_path);
	if(empty($list)){
		exit;
	}
	$list = explode("\n",$list);
	foreach($list as $key => $item){
		$list[$key] = unserialize($item);
	}
	usort($list,goodsCommentSort);
	
	foreach($list as $item){
		$cid = (int)FDB::resultFirst('SELECT comment_id FROM '.FDB::table('share_comment').' 
			WHERE share_id = '.$shareId." AND out_id = '".$item['out_id']."'");
		
		if(empty($cid)){
			$comment_id = FDB::insert('share_comment', $item, true);
			$comment_me = array();
			$comment_me['comment_id'] = $comment_id;
			$comment_me['uid'] = $uid;
			$comment_me['share_id'] = $shareId;
			FDB::insert('comment_me', $comment_me);
		}
		usleep(10);
	}
	//分享评论数量加n
	FDB::query('UPDATE '.FDB::table('share').' SET comment_count = comment_count + '.count($list).' WHERE share_id = '.$shareId);
	//清除分享评论列表缓存
	FS('share')->updateShareCache($shareId, 'comments');
	@unlink($cache_path);
}

//评论筛选
function checkContent($content){
	if(mb_strlen($content,'utf-8')<5){
		return false;
	}elseif($content=="此用户没有填写评论!"){
		return false;
	}elseif(strpos($content,"差")!==false){
		return false;
	}else{
		return true;
	}
}

function goodsCommentSort($a, $b){
    if ((int)$a['create_time'] == (int)$b['create_time'])
        return 0;
    return ((int)$a['create_time'] < (int)$b['create_time']) ? -1 : 1;
}
?>
