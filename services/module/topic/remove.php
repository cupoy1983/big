<?php
$tid = (int)$_FANWE['request']['tid'];

if($_FANWE['uid'] == 0 || !$tid)
	exit;

$is_group_admin = 0;
if($topic['fid'] > 0)
	$is_group_admin = FS('Group')->isAdminFromGroup($topic['fid'],$_FANWE['uid']);

if($is_group_admin == 0 && $topic['uid'] != $_FANWE['uid'])
	exit;

FS("Topic")->deleteTopic($tid,false);
$result['status'] = 1;
outputJson($result);
?>