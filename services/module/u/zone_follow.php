<?php
if($_FANWE['uid'] == 0)
	exit;

$user_list = FS('User')->getZoneFollowUsers();
$args = array('user_list'=>$user_list);	
echo trim(tplFetch("services/u/zone_follow",$args));
?>