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
	
if(FS('Activity')->getUserIsActivity($aid,$_FANWE['uid']) > -1)
	exit;

$fields = array();
$activity['fields'] = explode("\n",trim($activity['fields']));
foreach($activity['fields'] as $key => $name)
{
	if(!isset($_FANWE['request']['fields'][$key]))
		exit;
	
	$val = trim($_FANWE['request']['fields'][$key]);
	if(empty($val))
		exit;
	
	$fields[$name] = $val;
}

$data = array();
$data['fields_data'] = serialize($fields);
$data['content'] = htmlspecialchars(cutStr(trim($_FANWE['request']['message']),400,''));

$result = array();
$result['status'] = 0;
if(FS('Activity')->apply($aid,$_FANWE['uid'],$_FANWE['user_name'],$data))
{
	$result['status'] = 1;
}
outputJson($result);
?>