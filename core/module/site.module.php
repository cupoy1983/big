<?php
class SiteModule{
	
	public function tag(){
		global $_FANWE;
		$_FANWE['nav_title'] = "大东街标签库";
		$cache_file = getTplCache('page/site/tag');	
		if(!@include($cache_file)){
			$tags = array();
			foreach($_FANWE['cache']['goods_category']['parent'] as $cid){
				$tag_key = 'goods_category_tags_'.$cid;
				FanweService::instance()->cache->loadCache($tag_key);
				$tags[$cid] = $_FANWE['cache'][$tag_key];
			}
			include template('page/site/tag');
		}
		
		display($cache_file);
	}
	
	public function album(){
		global $_FANWE;
		$_FANWE['nav_title'] = "大东街杂志库";
		$cache_file = getTplCache('page/site/album');
		if(!@include($cache_file)){
			
			$data = array();
			$res = FDB::query("SELECT id ,name FROM ".FDB::table('album_category')." WHERE status = 1 ORDER BY id ASC");
			while($category = FDB::fetch($res)){
				$category['url'] = FU('album/category',array('id'=>$category['id']));
				
				$data[$category["id"]]["category"] = $category;
				$sql = 'SELECT id,title FROM '.FDB::table('album').' WHERE cid = '.$category["id"].' AND img_count > 0';
				$data[$category["id"]]["album"] = FDB::fetchAll($sql);
			}
			
			include template('page/site/album');
		}
		display($cache_file);
	}
}
?>