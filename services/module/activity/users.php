<?php
$id = intval($_FANWE['request']['id']);
if($id == 0)
	exit;

$activity = FS('Activity')->getById($id);
if(empty($activity))
	exit;

$activity_users_count = FS('Activity')->getUserCount($id);
$activity_users_pager = buildPageMini($activity_users_count,$_FANWE['page'],15);
$activity_users = FS('Activity')->getUsers($id,$activity_users_pager['limit']);

$result = array();
$result['status'] = 1;
$result['page_count'] = $activity_users_pager['page_count'];
$result['page'] = $activity_users_pager['page'];

$args['activity_users'] = $activity_users;
$result['html'] = tplFetch('services/activity/users',$args);
outputJson($result);
?>