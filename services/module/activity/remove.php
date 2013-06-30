<?php
if($_FANWE['uid'] == 0)
	exit;

$id = (int)$_FANWE['request']['id'];
if(!$id)
	exit;

$activity = FS('Activity')->getById($id);
if(empty($activity))
	exit;

if($activity['uid'] != $_FANWE['uid'])
	exit;

FS("Activity")->delete($id,false);
$result['status'] = 1;
outputJson($result);
?>