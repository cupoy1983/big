<?php
if($_FANWE['uid'] == 0)
	exit;

$vid = intval($_FANWE['request']['vid']);
if($vid == 0)
	exit;
	
$ids = trim($_FANWE['request']['ids']);
if(empty($ids))
	exit;

$vote = FS('Vote')->getById($vid);
if(empty($vote))
	exit;
	
if($vote['expiration_time'] > 0 && $vote['expiration_time'] < TIME_UTC)
	exit;
	
if(!FS('Vote')->getUserIsVote($vid,$_FANWE['uid']))
	exit;

$ids = explode(',',$ids);
$result = array();
$result['status'] = 0;
if(FS('Vote')->optionSubmit($vid,$_FANWE['uid'],$ids))
{
	$share = array();
	$share['share']['uid'] = $_FANWE['uid'];
	$share['share']['rec_id'] = $vid;
	$share['share']['content'] = sprintf(lang('vote','vote_option_share'),FU('vote/detail',array('id'=>$vid)),$vote['title']);
	FS('Share')->save($share);

	$nums = FDB::fetchFirst('SELECT num,users FROM '.FDB::table('vote').' WHERE id = '.$vid);
	$result['users'] = $nums['users'];
	$result['status'] = 1;
	$args = array(
		'vote'=>$vote,
		'vote_options'=>FS('Vote')->getOptions($vid,$nums['num'])
	);
	$result['html'] = tplFetch('services/vote/vote_detail',$args);
	$args = array(
		'vote_users'=>FS('Vote')->getUsers($vid,9)
	);
	$result['userhtml'] = tplFetch('services/vote/vote_users',$args);
}
outputJson($result);
?>