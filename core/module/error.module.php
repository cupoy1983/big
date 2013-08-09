<?php
class ErrorModule{
	
	public function _404(){
		global $_FANWE;
		$_FANWE['nav_title'] = "404-找不到该页面";
		$cache_file = getTplCache('page/error/404');
		if(!@include($cache_file)){
			include template('page/error/404');
		}
		display($cache_file);
	}
}
?>