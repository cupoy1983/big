<?php
$url = trim($_FANWE['request']['url']);
if(empty($url))
	exit;
$url = urldecode($url);

$callback = trim($_FANWE['request']['callback']);	
if(empty($callback))
	exit;

$result = array();
$result['status'] = 1;
$result['uid'] = $_FANWE['uid'];
if($result['uid'] == 0)
{
	echo $callback.'('.getJson($result).');';
	exit;
}

require fimport('service/sharegoods');
$share_module = new SharegoodsService($url);
$goods = $share_module->fetch();
if($goods)
{
	$result['goods'] = 1;
	$result['status'] = $goods['status'];
}
else
	$result['goods'] = 0;
echo $callback.'('.getJson($result).');';
?>