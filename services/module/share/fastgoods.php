<?php
$cache_file = getTplCache('services/share/fast_goods');
if(!@include($cache_file))
{
	FanweService::instance()->cache->loadCache('business');
	$business = $_FANWE['cache']['business'];
	unset($business['yiqifa']);
	include template('services/share/fast_goods');
}			
display($cache_file);
?>