<?php
$cache_file = getTplCache('services/share/fast_photo');
if(!@include($cache_file))
	include template('services/share/fast_photo');		
display($cache_file);
?>