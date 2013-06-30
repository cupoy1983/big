<?php
if($_FANWE['uid'] == 0)
	exit;

$id = intval($_FANWE['request']['id']);
if($id == 0)
	exit;

$activity = FS('Activity')->getById($id);
if(empty($activity))
	exit;

$apply_status = FS('Activity')->getUserIsActivity($id,$_FANWE['uid']);
if($apply_status != 1 && $activity['uid'] != $_FANWE['uid'])
	exit;

unset($_FANWE['request']['share_id']);
$_FANWE['request']['uid'] = $_FANWE['uid'];
$_FANWE['request']['rec_id'] = $id;
$_FANWE['request']['parent_id'] = $activity['share_id'];
$_FANWE['request']['type'] = 'activity_post';
$_FANWE['request']['title'] = addslashes($activity['title']);
$_FANWE['request']['content'] = cutStr($_FANWE['request']['content'],280,'');
$result = array();
if(!checkIpOperation("add_share",SHARE_INTERVAL_TIME))
{
	$result['status'] = 0;
	$result['error_msg'] = lang('share','interval_tips');
	outputJson($result);
}

$share = FS('Share')->submit($_FANWE['request']);
if($share['status'])
{
	$content = htmlspecialchars(trim($_FANWE['request']['content']));
	$post_id = (int)FS('Activity')->savePost($id,$content,$share['share_id']);
	
	$result['status'] = 1;
	$list[] = FS('Share')->getShareById($share['share_id'],false);
	$list = FS('Share')->getShareDetailList($list,true,true,true);
	$args = array(
		'share_item'=>current($list),
		'current_share_id'=>$vote['share_id']
	);
	$result['html'] = tplFetch('services/share/share_item',$args);
}
else
{
	$result['status'] = 0;
	$result['error_code'] = $share['error_code'];
	$result['error_msg'] = $share['error_msg'];
}

outputJson($result);
?>