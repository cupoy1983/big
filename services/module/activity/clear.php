<?php
if($_FANWE['uid'] == 0)
	exit;

$aid = intval($_FANWE['request']['aid']);
if($aid == 0)
	exit;

$activity = FS('Activity')->getById($aid);
if(empty($activity))
	exit;
	
if($activity['expiration_time'] > 0 && $activity['expiration_time'] < TIME_UTC)
	exit;

$clear_message = htmlspecialchars(cutStr(trim($_FANWE['request']['message']),400,''));
$result = array();
$result['status'] = 1;
FS('Activity')->clearApply($aid,$_FANWE['uid'],$_FANWE['user_name'],$clear_message);
outputJson($result);
?>