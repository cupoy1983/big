<?php
//未登陆直接退出
$uid = $_FANWE['uid'];
if($uid == 0)
	exit;

$cache_key = md5(http_build_query($_FANWE['request']));
$cache_key = 'upublish/'.substr($cache_key,0,2).'/'.substr($cache_key,2,2).'/'.$cache_key;
$html = getCache($cache_key);
if($html == NULL)
{
	$type = $_FANWE['request']['type'];
	if(empty($type))
		exit;
	
	Cache::getInstance()->loadCache('goods_color');
	Cache::getInstance()->loadCache('activity_cate');
	
	$cate_options = '';
	FS('Share')->getShareCateOptions(0,$cate_options,0);
	
	$args = array('type'=>$type,'cate_options'=>$cate_options);
	$html = trim(tplFetch("services/u/publish_share",$args));
	//setCache($cache_key,$html,SHARE_CACHE_TIME);
}
echo $html;
?>