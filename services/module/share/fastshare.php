<?php
$cache_file = getTplCache('services/share/fast_share');
if(!@include($cache_file))
	include template('services/share/fast_share');		
display($cache_file);
?>