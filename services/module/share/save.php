<?php
$_FANWE['request']['uid'] = $_FANWE['uid'];

$result = array();
if(!checkIpOperation("add_share",SHARE_INTERVAL_TIME))
{
	$result['status'] = 0;
	$result['error_msg'] = lang('share','interval_tips');
	outputJson($result);
}

if(empty($_FANWE['request']['content']))
	exit;

$goods_count = 0;
$photo_count = 0;
if(isset($_FANWE['request']['goods']) && is_array($_FANWE['request']['goods']))
	$goods_count = count($_FANWE['request']['goods']);
	
if(isset($_FANWE['request']['pics']) && is_array($_FANWE['request']['pics']))
	$photo_count = count($_FANWE['request']['pics']);
	
if((int)$_FANWE['request']['albumid'] > 0 && $goods_count == 0 && $photo_count == 0)
	exit;

$share_type = $_FANWE['request']['share_type'];
switch($share_type)
{
	case "look":
		if($goods_count == 0 || $photo_count == 0)
			exit;
	break;
	case "dapei":
		if($photo_count == 0)
			exit;
	break;
	case "activity":
		if(empty($_FANWE['request']['title']))
			exit;
		
		$today_time = getTodayTime();
		$begin_time = str2Time($_FANWE['request']['begin_time']);
		$time_range = (int)$_FANWE['request']['time_range'];
		if($time_range == 0)
		{
			if($begin_time < $today_time)
				exit;
		}
		else
		{
			$end_time = str2Time($_FANWE['request']['end_time']);
			if($begin_time == 0 || $end_time < $today_time)
				exit;
		}
		
		if((int)$_FANWE['request']['cid'] == 0)
			exit;
			
		$_FANWE['request']['type']="activity";
	break;
	case "vote":
		if(empty($_FANWE['request']['title']))
			exit;
		
		$vote_count = 0;
		if(isset($_FANWE['request']['vote_item']) && is_array($_FANWE['request']['vote_item']))
		{
			foreach($_FANWE['request']['vote_item'] as $vote_item)
			{
				if(!empty($vote_item))
					$vote_count++;
			}
		}
		
		if($vote_count < 2 || $vote_count > 20)
			exit;
			
		$_FANWE['request']['type']="vote";
	break;
}

if($share_type == 'activity' || $share_type == 'vote')
	$_FANWE['request']['content'] = cutStr($_FANWE['request']['content'],1000,'');
else
	$_FANWE['request']['content'] = cutStr($_FANWE['request']['content'],280,'');

$share = FS('Share')->submit($_FANWE['request'],true,true);
if($share['status'])
{
	$_FANWE['request']['share_id'] = $share['share_id'];
	switch($share_type)
	{
		case "activity":
			$id = FS('Activity')->save($_FANWE['request']);
			if($id > 0)
				FDB::query('UPDATE '.FDB::table('share').' SET rec_id = '.$id.' WHERE share_id = '.$share['share_id']);
			else
				FDB::query('UPDATE '.FDB::table('share').' SET type = \'default\' WHERE share_id = '.$share['share_id']);
		break;
		case "vote":
			$id = FS('Vote')->save($_FANWE['request']);
			if($id > 0)
				FDB::query('UPDATE '.FDB::table('share').' SET rec_id = '.$id.' WHERE share_id = '.$share['share_id']);
			else
				FDB::query('UPDATE '.FDB::table('share').' SET type = \'default\' WHERE share_id = '.$share['share_id']);
		break;
	}
	$result['share_id'] = $share['share_id'];
	$result['url'] = FU('note/index',array('sid'=>$share['share_id']));
	$result['status'] = 1;
	$result['error_code'] = $share['error_code'];
	$result['error_msg'] = $share['error_msg'];
	$list = array();
	
	$list[] = FS('Share')->getShareById($share['share_id'],false);
	$list = FS('Share')->getShareDetailList($list,true,true,true);
	
	$args = array(
		'share_item'=>current($list),
	);
	
	$result['html'] = tplFetch('services/share/u_share_item',$args);
}
else
{
	$result['status'] = 0;
	$result['error_code'] = $share['error_code'];
	$result['error_msg'] = $share['error_msg'];
}

outputJson($result);
?>