<?php
if($_FANWE['uid'] == 0)
	exit;

$aid = intval($_FANWE['request']['aid']);
if($aid == 0)
	exit;

$ids = trim($_FANWE['request']['ids']);
if(empty($ids))
	exit;
	
$ids = explode(',',$ids);
	
if(!isset($_FANWE['request']['status']))
	exit;

$status = (int)$_FANWE['request']['status'];

$activity = FS('Activity')->getById($aid);
if(empty($activity))
	exit;
	
if($activity['expiration_time'] > 0 && $activity['expiration_time'] < TIME_UTC)
	exit;

$message = htmlspecialchars(cutStr(trim($_FANWE['request']['message']),400,''));
$result = array();
$result['status'] = 1;
FS('Activity')->manage($aid,$ids,$status,$message);
outputJson($result);
?>