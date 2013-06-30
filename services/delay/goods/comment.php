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
$id = (int)$_FANWE['request']['id'];
$goods = FS('Goods')->getById($id);


if(!$goods)
	exit;

setTimeLimit(120);
$today_time = getTodayTime();
if($goods['comment_collect_time'] < $today_time)
{
	$sql = 'UPDATE '.FDB::table('goods').' SET comment_collect_time = '.$today_time.' WHERE id = '.$id;
	FDB::query($sql);
	if(FDB::affectedRows() > 0)
	{
		$cache_path = PUBLIC_ROOT.'data/caches/custom/goods/'.getDirsById($id).'/collect/comment.php';
		$phth = dirname($cache_path);
		makeDir($phth);
		$temp_content = '';
		
		if($goods['type'] == 'taobao')
		{
			$url = $goods['url'];
			$content = getUrlContent($url,true);
			preg_match("/\"valReviewsApi\":\"(.+?)\"/",$content,$comment_url);
			if(!$comment_url)
				preg_match("/\"apiTmallReview\":\"(.+?)\"/",$content,$comment_url);

			if($comment_url)
			{
				$comment_url = stripslashes(trim($comment_url[1]));
				$comments = gbToUTF8(getUrlContent($comment_url));
				
				$pos = strpos($comments,'{');
				$comments = substr($comments,$pos);
				$comments = json_decode($comments,true);

				foreach($comments['rateListInfo']['rateList'] as $rate)
				{
					$item = array();
					$item['goods_id'] = $id;
					$item['commont_id'] = $rate['id'];
					$item['user_name'] = $rate['displayUserNick'];
					$item['avatar'] = 'http://wwc.taobaocdn.com/avatar/getAvatar.do?userId='.$rate['displayUserNumId'].'&width=80&height=80&type=sns';
					$item['content'] = preg_replace("/[\r\n]/",'',$rate['rateContent']);
					$item['content'] = FS('Goods')->generateGoodsName($item['content']);
					$item['create_time'] = str2Time(str_replace('.','-',$rate['rateDate']));
					if(!empty($temp_content))
						$temp_content .= "\n".serialize($item);
					else
						$temp_content .= serialize($item);
				}
			}
			else
			{
				$content=preg_replace("/[\r\n]/",'',$content);
				preg_match("/TShop\.Setup\(.*?\"api\":(.*?)\"tradeType\":/",$content,$taobao_json);
				if($taobao_json)
				{
					$taobao_json = '{api:'.substr(trim($taobao_json[1]),0,-1).'}';
					require_once fimport('class/sjson');
					$json = new Services_JSON();
					$taobao_json = $json->decode($taobao_json);
					if($taobao_json && isset($taobao_json->api) && $taobao_json->itemDO && $taobao_json->url)
					{
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
						
						foreach($comments['rateDetail']['rateList'] as $rate)
						{
							$item = array();
							$item['goods_id'] = $id;
							$item['commont_id'] = $rate['id'];
							$item['user_name'] = $rate['displayUserNick'];
							$item['avatar'] = 'http://wwc.taobaocdn.com/avatar/getAvatar.do?userId='.$rate['displayUserNumId'].'&width=80&height=80&type=sns';
							$item['content'] = preg_replace("/[\r\n]/",'',$rate['rateContent']);
							$item['content'] = FS('Goods')->generateGoodsName($item['content']);
							$item['create_time'] = str2Time(str_replace('.','-',$rate['rateDate']));
							if(!empty($temp_content))
								$temp_content .= "\n".serialize($item);
							else
								$temp_content .= serialize($item);
						}
					}
				}
			}

		}
		elseif($goods['type'] == 'paipai')
		{
			$key = str_replace('paipai_','',$goods['keyid']);
			$comment_url = 'http://shop1.paipai.com/cgi-bin/creditinfo/CmdyEval?sCmdyId='.$key.'&nCurPage=1&nTotal=200&resettime=1';
			$comments = gbToUTF8(getUrlContent($comment_url));
			$pos = strpos($comments,'{');
			$end = strpos($comments,';try{');
			$comments = substr($comments,$pos,$end - $pos);
			require_once fimport('class/sjson');
			$json = new Services_JSON();
			$comments = $json->decode($comments);
			if(count($comments->evalList) == 0)
				exit;
			
			foreach($comments->evalList as $rate)
			{
				if($rate)
				{
					$rate = (array)$rate;
					$item = array();
					$item['goods_id'] = $id;
					$item['commont_id'] = $rate['sDealId'];
					$item['user_name'] = $rate['buyerName'];
					$item['avatar'] = '';
					$item['content'] = preg_replace("/[\r\n]/",'',$rate['peerEvalContent']);
					$item['content'] = FS('Goods')->generateGoodsName($item['content']);
					$item['create_time'] = str2Time($rate['peerTime']);
					if(!empty($temp_content))
						$temp_content .= "\n".serialize($item);
					else
						$temp_content .= serialize($item);
				}
			}
		}
		
		if(!empty($temp_content))
		{
			writeFile($cache_path,$temp_content);
			
			sleep(1);
			$args = array('m'=>'goods','a'=>'comment','id'=>$id,'page'=>2,'comment_url'=>$comment_url);
			FS('Delay')->create($args,false);
		}
	}
}
else
{
	$comment_url = $_FANWE['request']['comment_url'];
	if(empty($comment_url))
	{
		insertGoodsComment($id);
		exit;
	}
	
	$cache_path = PUBLIC_ROOT.'data/caches/custom/goods/'.getDirsById($id).'/collect/comment.php';
	$temp_content = '';
	
	$_FANWE['request']['page'] = (int)$_FANWE['request']['page'];
	if($goods['type'] == 'taobao')
	{
		$comment_url = preg_replace("/&currentPage\=(\d+)&/",'&currentPage='.$_FANWE['request']['page'].'&',$comment_url);
		$comments = trim(gbToUTF8(getUrlContent($comment_url)));
		$pos = strpos($comments,'{');
		if(strpos($comments,'jsonp') === 0)
			$comments = substr($comments,$pos,-1);
		else
			$comments = substr($comments,$pos);

		$comments = json_decode($comments,true);
		
		if(isset($comments['rateListInfo']))
		{
			$beginIndex = (int)$comments['rateListInfo']['paginator']['beginIndex'];
			if($beginIndex < 2)
			{
				insertGoodsComment($id);
				exit;
			}
			
			foreach($comments['rateListInfo']['rateList'] as $rate)
			{
				$item = array();
				$item['goods_id'] = $id;
				$item['commont_id'] = $rate['id'];
				$item['user_name'] = $rate['displayUserNick'];
				$item['avatar'] = 'http://wwc.taobaocdn.com/avatar/getAvatar.do?userId='.$rate['displayUserNumId'].'&width=80&height=80&type=sns';
				$item['content'] = preg_replace("/[\r\n]/",'',$rate['rateContent']);
				$item['content'] = FS('Goods')->generateGoodsName($item['content']);
				$item['create_time'] = str2Time(str_replace('.','-',$rate['rateDate']));
				$temp_content .= "\n".serialize($item);
			}
		}
		else
		{
			foreach($comments['rateDetail']['rateList'] as $rate)
			{
				$item = array();
				$item['goods_id'] = $id;
				$item['commont_id'] = $rate['id'];
				$item['user_name'] = $rate['displayUserNick'];
				$item['avatar'] = 'http://wwc.taobaocdn.com/avatar/getAvatar.do?userId='.$rate['displayUserNumId'].'&width=80&height=80&type=sns';
				$item['content'] = preg_replace("/[\r\n]/",'',$rate['rateContent']);
				$item['content'] = FS('Goods')->generateGoodsName($item['content']);
				$item['create_time'] = str2Time(str_replace('.','-',$rate['rateDate']));
				$temp_content .= "\n".serialize($item);
			}

			$lastpage = (int)$comments['rateDetail']['paginator']['lastPage'];
			if($_FANWE['request']['page'] >= $lastpage)
			{
				insertGoodsComment($id);
				exit;
			}
		}
	}
	elseif($goods['type'] == 'paipai')
	{
		$comment_url = preg_replace("/&nCurPage\=(\d+)&/",'&nCurPage='.$_FANWE['request']['page'].'&',$comment_url);
		$comments = gbToUTF8(getUrlContent($comment_url));
		$pos = strpos($comments,'{');
		$end = strpos($comments,';try{');
		$comments = substr($comments,$pos,$end - $pos);
		require_once fimport('class/sjson');
		$json = new Services_JSON();
		$comments = $json->decode($comments);
		
		if(count($comments->evalList) == 0)
		{
			insertGoodsComment($id);
			exit;
		}
		
		foreach($comments->evalList as $rate)
		{
			if($rate)
			{
				$rate = (array)$rate;
				$item = array();
				$item['goods_id'] = $id;
				$item['commont_id'] = $rate['sDealId'];
				$item['user_name'] = $rate['buyerName'];
				$item['avatar'] = '';
				$item['content'] = preg_replace("/[\r\n]/",'',$rate['peerEvalContent']);
				$item['content'] = FS('Goods')->generateGoodsName($item['content']);
				$item['create_time'] = str2Time($rate['peerTime']);
				$temp_content .= "\n".serialize($item);
			}
		}
	}
	
	if(!empty($temp_content))
		writeFile($cache_path,$temp_content,'a');
	
	if($_FANWE['request']['page'] > 5 || empty($temp_content))
	{
		insertGoodsComment($id);
		exit;
	}
	
	sleep(1);
	$_FANWE['request']['page']++;
	$args = array('m'=>'goods','a'=>'comment','id'=>$id,'page'=>$_FANWE['request']['page'],'comment_url'=>$comment_url);
	FS('Delay')->create($args,false);
}

function insertGoodsComment($id)
{
	$cache_path = PUBLIC_ROOT.'data/caches/custom/goods/'.getDirsById($id).'/collect/comment.php';
	$list = @file_get_contents($cache_path);
	$list = explode("\n",$list);
	foreach($list as $key => $item)
	{
		$list[$key] = unserialize($item);
	}
	usort($list,goodsCommentSort);
	
	foreach($list as $item)
	{
		$cid = (int)FDB::resultFirst('SELECT id FROM '.FDB::table('goods_comment').' 
			WHERE goods_id = '.$id." AND commont_id = '".$item['commont_id']."'");
		if($cid > 0)
		{
			$item['id'] = $cid;
			FDB::insert('goods_comment',$item,false,true);
		}
		else
			FDB::insert('goods_comment',$item);
		
		usleep(10);
	}
	@unlink($cache_path);
}

function goodsCommentSort($a, $b)
{
    if ((int)$a['create_time'] == (int)$b['create_time'])
        return 0;
    return ((int)$a['create_time'] < (int)$b['create_time']) ? -1 : 1;
}
?>
