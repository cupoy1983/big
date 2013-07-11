<?php

$tid = intval($_FANWE['request']['tid']);

if($tid == 0 || $_FANWE['uid'] == 0){
	exit;
}

$topic = FS('Topic')->getTopicById($tid);
if(empty($topic)){
	exit;
}
	
if($topic['fid'] > 0)
{
	if(FS('Group')->isUserFromGroup($topic['fid'],$_FANWE['uid']) != 1)
	{
		$result['status'] = 0;
		$result['error_msg'] = lang('group','no_group_error');
		outputJson($result);
	}
}

$_FANWE['request']['uid'] = $_FANWE['uid'];
$_FANWE['request']['title'] = addslashes($topic['title']);
$result = array();
if(!checkIpOperation("add_share",SHARE_INTERVAL_TIME))
{
	$result['status'] = 0;
	$result['error_msg'] = lang('share','interval_tips');
	outputJson($result);
}

$content = trim($_FANWE['request']['content']);
$post_id = (int)FS('Topic')->saveTopicPost($tid, $content);
$list['pid'] = $post_id;
$list['uid'] = $_FANWE['uid'];
$list['time']= TIME_UTC;
$list['content'] = stripslashes($content);
$result['status'] = 1;

$args = array(
	'post_item'=>$list,
	'current_post_id'=>$topic['share_id']
);
$result['html'] = tplFetch('services/topic/post_item',$args);

outputJson($result);
?>