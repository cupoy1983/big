<?php
if($_FANWE['uid'] == 0)
	exit;

$id = (int)$_FANWE['request']['id'];
if(!$id)
	exit;

$vote = FS("Vote")->getById($id);
if(empty($vote))
	exit;

if($vote['uid'] != $_FANWE['uid'])
	exit;

FS("Vote")->delete($id,false);
$result['status'] = 1;
outputJson($result);
?>