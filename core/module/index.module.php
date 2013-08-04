<?php
class IndexModule
{
	public function index()
	{
		global $_FANWE;
		$firstAccess = false;
		if(empty($_FANWE['cookie']['firstAccess'])){
			$firstAccess = true;
			fSetCookie('firstAccess', 1, 0, 1, true);
		}
		
		clearTempImage();
		$cache_file = getTplCache('page/index_index',array("firstAccess"=>$firstAccess),2);
		if(getCacheIsUpdate($cache_file,SHARE_CACHE_TIME,1))
		{
//          modified by frankie 删除模板无用数据
// 			$look_bests = FS('Look')->getBest();
// 			$dapei_bests = FS('Dapei')->getBest();
// 			$today_daren = FS('Daren')->getIndexTodayDaren();
// 			$group_list = FS('Group')->getGroupsByType('users',20);
// 			if(count($group_list) > 0)
// 				$group_list = array_chunk($group_list,4);
	
// 			$topic_list = FS('Topic')->getTopicsByType('best',0,6,true);
			$best_albums = FS('Album')->getBestAlbums(15);
			Cache::getInstance()->loadCache('albums');
			
			include template('page/index_index');
			display($cache_file);
		}
		else
		{
			include $cache_file;
			display();
		}
	}
}
?>