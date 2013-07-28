<?php
class AboutModule{
	public function about(){
		global $_FANWE;
		$_FANWE['nav_title'] = "关于我们";
		$cache_file = getTplCache('page/about/about');
		if(!@include($cache_file)){
			include template('page/about/about');
		}
		display($cache_file);
	}
	
	public function contact(){
		global $_FANWE;
		$_FANWE['nav_title'] = "联系我们";
		$cache_file = getTplCache('page/about/contact');
		if(!@include($cache_file)){
			include template('page/about/contact');
		}
		display($cache_file);
	}
	
	public function link(){
		global $_FANWE;
		$_FANWE['nav_title'] = "友情链接";
		$cache_file = getTplCache('page/about/link');
		if(!@include($cache_file)){
			include template('page/about/link');
		}
		display($cache_file);
	}
	
	public function help(){
		global $_FANWE;
		$_FANWE['nav_title'] = "获取帮助";
		$cache_file = getTplCache('page/about/help');
		if(!@include($cache_file)){
			include template('page/about/help');
		}
		display($cache_file);
	}
	
	public function agreement(){
		global $_FANWE;
		$_FANWE['nav_title'] = "用户协议";
		$cache_file = getTplCache('page/about/agreement');
		if(!@include($cache_file)){
			include template('page/about/agreement');
		}
		display($cache_file);
	}
	
	public function privacy(){
		global $_FANWE;
		$_FANWE['nav_title'] = "隐私政策";
		$cache_file = getTplCache('page/about/privacy');
		if(!@include($cache_file)){
			include template('page/about/privacy');
		}
		display($cache_file);
	}
	
	public function disclaimer(){
		global $_FANWE;
		$_FANWE['nav_title'] = "免责申明";
		$cache_file = getTplCache('page/about/disclaimer');
		if(!@include($cache_file)){
			include template('page/about/disclaimer');
		}
		display($cache_file);
	}
	
	public function iphone(){
		global $_FANWE;
		$_FANWE['nav_title'] = "iphone客户端";
		$cache_file = getTplCache('page/about/iphone');
		if(!@include($cache_file)){
			include template('page/about/iphone');
		}
		display($cache_file);
	}
	
	public function android(){
		global $_FANWE;
		$_FANWE['nav_title'] = "android客户端";
		$cache_file = getTplCache('page/about/android');
		if(!@include($cache_file)){
			include template('page/about/android');
		}
		display($cache_file);
	}
}
?>