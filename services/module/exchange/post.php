<?php
if($_FANWE['uid'] == 0)
	exit;

$id = intval($_FANWE['request']['id']);
if($id == 0)
	exit;

$exchange = FS('Exchange')->getById($id);
if(empty($exchange))
	exit;

unset($_FANWE['request']['share_id']);
$_FANWE['request']['uid'] = $_FANWE['uid'];
$_FANWE['request']['rec_id'] = $id;
$_FANWE['request']['parent_id'] = $activity['share_id'];
$_FANWE['request']['type'] = 'trial_post';
$_FANWE['request']['title'] = addslashes($exchange['name']);
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
	$post = array();
	$post['eid'] = $id;
	$post['share_id'] = $share['share_id'];
	$post['uid'] = $_FANWE['uid'];
	$post['type'] = 1;
	$post['content'] = htmlspecialchars($_FANWE['request']['content']);
	$post['create_time'] = TIME_UTC;
	FDB::insert('exchange_post',$post);
	
	FDB::query('UPDATE '.FDB::table('user_count').' SET
				trial_post = trial_post + 1
				WHERE uid = '.$post['uid']);
	FS('Medal')->runAuto($post['uid'],'trial_post');
	
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