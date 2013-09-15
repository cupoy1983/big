<?php
class AlbumModule
{
	public function index()
	{
		global $_FANWE;
		
		$cache_file = getTplCache('page/album/album_index',$_FANWE['request'],2);
		if(getCacheIsUpdate($cache_file,SHARE_CACHE_TIME,1))
		{
			//专辑flash
			$flash_album = FS("Album")->getFlashAlbums(3);
			
			//推荐专辑
			$best_album = FS("Album")->getBestAlbums(6);
			
			//最新专辑作者
			$new_users = FS("Album")->getNewUsers(6);
			
			//最热专辑作者
			$hot_users = FS("Album")->getHotUsers(6);
			
			$page_args = array();
			$sort = $_FANWE['request']['sort'];
			$order = '';
			switch($sort)
			{
				case 'hot':
					$page_args['sort'] = 'hot';
					$order = " ORDER BY id DESC";
				break;
				default:
					$sort = 'new';
					$page_args['sort'] = 'new';
					$order = " ORDER BY collect_count DESC,id DESC";
				break;
			}
			
			$sql = 'SELECT COUNT(id) FROM '.FDB::table('album').' WHERE img_count > 0';
			$count = FDB::resultFirst($sql);
			$album_list = array();
			
			if($count > 0)
			{
				$pager = buildPage('album/'.ACTION_NAME,$page_args,$count,$_FANWE['page'],40);
				$sql = 'SELECT id,cid,share_id,uid,title,content,img_count,collect_count,create_time,is_best,best_img,cache_data FROM '.FDB::table('album').' WHERE img_count > 0'.$order.' LIMIT '.$pager['limit'];
				$res = FDB::query($sql);
				while($data = FDB::fetch($res))
				{
					$data['url'] = FU('album/show',array('id'=>$data['id']));
					$album_list[$data['id']] = $data;
				}
				FS('Album')->albumImagesFormat($album_list);
			}
			
			include template('page/album/album_index');
			display($cache_file);
			exit;
		}
		else
		{
			include $cache_file;
			display();
		}
	}
	
	public function show()
	{
		global $_FANWE;
		$id = (int)$_FANWE['request']['id'];
		if(!$id)
			exit;
		
		$album = FS("Album")->getAlbumById($id);
		if(empty($album))
			fHeader("location: ".FU('album'));
		
		$album_share = FS("Share")->getShareById($album['share_id']);
		$album_comments = FS('Share')->getShareCommentList($album['share_id'],'0,5');
		
		$album_cate = $_FANWE['cache']['albums']['category'][$album['cid']];
		
		$_FANWE['PAGE_SEO_SELF'] = $album;
		$_FANWE['PAGE_SEO_SELF']['tags'] = $_FANWE['PAGE_SEO_SELF']['tags_str'];
		unset($_FANWE['PAGE_SEO_SELF']['cache_data'],$_FANWE['PAGE_SEO_SELF']['tags_str']);
		
		
		$album_user = FS('User')->getUserById($album['uid']);
		$_FANWE['PAGE_SEO_SELF']['user_name'] = $album_user['user_name'];
		
		if(isset($_FANWE['cache']['albums']['category'][$album['cid']]))
		{
			$_FANWE['PAGE_SEO_SELF']['cate_name'] = $_FANWE['cache']['albums']['category'][$album['cid']]['name'];
		}
		
		$is_follow_user = false;
		$is_best_album = false;
		if($_FANWE['uid'] > 0 && $_FANWE['uid'] != $album['uid'])
		{
			$is_best_album = FS('Album')->getIsBest($id,$_FANWE['uid']);
		}
		$is_manage_album = false;
		if($_FANWE['uid'] == $album['uid'])
			$is_manage_album = true;
		
		$page_args = array();
		$page_args['id'] = $id;
		
		$count = $album['share_count'];
		$$share_list = array();
		if($count > 0)
		{
			$sid = (int)$_FANWE['request']['sid'];
			$page_size = (int)$_FANWE['setting']['share_pb_item_count'] * (int)$_FANWE['setting']['share_pb_load_count'];
			$pager = buildPage('album/show',$page_args,$count,$_FANWE['page'],$page_size);
			
			$share_ids = array();
			if($sid > 0)
			{
				$sql = 'SELECT share_id FROM '.FDB::table('album_share_index').' 
					WHERE album_id = '.$id.' AND share_id = '.$sid;
				$sid = (int)FDB::resultFirst($sql);
				$share_ids[] = $sid;
			}
			
			$page_args['page'] = $_FANWE['page'];
			$page_args['pindex'] = '_pindex_';
			$pb_url = $_FANWE['site_root'].'services/service.php?m=album&a=show&'.http_build_query($page_args);
			$pb_list = array();
			if($count > $_FANWE['setting']['share_pb_item_count'])
			{
				for($i = 2;$i <= $_FANWE['setting']['share_pb_load_count'];$i++)
				{
					$pb_list[] = str_replace('_pindex_',$i,$pb_url);
				}
			}
			
			$res = FDB::query('SELECT share_id FROM '.FDB::table('album_share_index').' 
				WHERE album_id = '.$id.' ORDER BY share_id DESC LIMIT '.($_FANWE['page'] - 1) * $pager['page_size'] . "," . $_FANWE['setting']['share_pb_item_count']);
			while($data = FDB::fetch($res))
			{
				if($data['share_id'] != $sid)
					$share_ids[] = $data['share_id'];
			}
			
			if(count($share_ids) > 0)
			{
				$share_ids = implode(',',$share_ids);
				$share_list = FDB::fetchAll('SELECT * FROM '.FDB::table('share').'  
					WHERE share_id IN ('.$share_ids.') ORDER BY share_id DESC');
				$share_list = FS('Share')->getShareDetailList($share_list,false,false,false,true,2);
				
				if($sid > 0)
				{
					$share = $share_list[$sid];
					unset($share_list[$sid]);
					array_unshift($share_list,$share);
				}
			}
		}
		
		$other_album = array();
		if($album_user['albums'] > 1)
		{
			$sql = 'SELECT * FROM '.FDB::table('album').' 
				WHERE uid = '.$album_user['uid'].' AND img_count > 0 AND id <> '.$id.' LIMIT 0,3';
			$res = FDB::query($sql);
			while($data = FDB::fetch($res))
			{
				$data['url'] = FU('album/show',array('id'=>$data['id']));
				$other_album[] = $data;
			}
			FS('Album')->albumImagesFormat($other_album);
		}
		$album["img"] = FS('Image')->formatById($album["img"]);
		
		include template('page/album/album_show');
		display();
	}

	public function category()
	{
		global $_FANWE;
		$cache_file = getTplCache('page/album/album_category',$_FANWE['request'],2);
		if(getCacheIsUpdate($cache_file,SHARE_CACHE_TIME,1))
		{
			$id = (int)$_FANWE['request']['id'];
			if(!$id)
				fHeader("location: ".FU('album'));
				
			if(!isset($_FANWE['cache']['albums']['category'][$id]))
				fHeader("location: ".FU('album'));
			
			$album_cate = $_FANWE['cache']['albums']['category'][$id];
			
			$_FANWE['PAGE_SEO_SELF'] = $album_cate;
			
			$page_args = array();
			$page_args['id'] = $id;
			
			$sort = $_FANWE['request']['sort'];
			$order = '';
			switch($sort)
			{
				case 'hot':
					$page_args['sort'] = 'hot';
					$order = " ORDER BY id DESC";
				break;
				default:
					$sort = 'new';
					$page_args['sort'] = 'new';
					$order = " ORDER BY collect_count DESC,id DESC";
				break;
			}
			
			$where = ' WHERE cid = '.$id.' AND img_count > 0';
			
			$sql = 'SELECT COUNT(id) FROM '.FDB::table('album').$where;
			$count = FDB::resultFirst($sql);
			$album_list = array();
			
			if($count > 0)
			{
				$pager = buildPage('album/'.ACTION_NAME,$page_args,$count,$_FANWE['page'],40);
				$sql = 'SELECT id,cid,share_id,uid,title,content,img_count,collect_count,create_time,is_best,best_img,cache_data FROM '.FDB::table('album').$where.$order.' LIMIT '.$pager['limit'];
				$res = FDB::query($sql);
				while($data = FDB::fetch($res))
				{
					$data['url'] = FU('album/show',array('id'=>$data['id']));
					$album_list[$data['id']] = $data;
				}
				FS('Album')->albumImagesFormat($album_list);
			}
		
			include template('page/album/album_category');
			display($cache_file);
			exit;
		}
		else
		{
			include $cache_file;
			display();
		}
	}
	
	public function tag()
	{
		global $_FANWE;
		global $_FANWE;
		$tag = trim($_FANWE['request']['tag']);
		if(empty($tag))
			fHeader("location: ".FU('album'));
	}

	public function create()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
			fHeader("location: ".FU('user/login'));
		
		include template('page/album/album_create');
		display();
	}
	
	public function edit()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
			fHeader("location: ".FU('user/login'));
			
		$id = (int)$_FANWE['request']['id'];
		if(!$id)
			fHeader("location: ".FU('album'));
			
		$album = FS("Album")->getAlbumById($id);
		if(empty($album) || $album['uid'] != $_FANWE['uid'])
			fHeader("location: ".FU('album'));
		
		$album['tags'] = implode(' ',$album['tags']);
		include template('page/album/album_edit');
		display();
	}

	public function save()
	{
		global $_FANWE;
		
		if($_FANWE['uid'] == 0)
			fHeader("location: ".FU('user/login'));
			
		$id = (int)$_FANWE['request']['id'];
		if($id > 0)
		{
			$album = FS("Album")->getAlbumById($id);
			if(empty($album) || $album['uid'] != $_FANWE['uid'])
				fHeader("location: ".FU('album'));
		}
			
		$data = array(
			'title'        => trim($_FANWE['request']['title']),
			'content'      => trim($_FANWE['request']['content']),
			'cid'          => (int)$_FANWE['request']['cid'],
			'show_type'    => (int)$_FANWE['request']['show_type'],
			'tags'         => trim($_FANWE['request']['tags']),
		);
		
		$vservice = FS('Validate');
		$validate = array(
			array('title','required',lang('album','name_require')),
			array('title','max_length',lang('album','name_max'),60),
			array('content','max_length',lang('album','content_max'),1000),
			array('cid','min',lang('album','cid_min'),1),
			array('show_type','min',lang('album','show_type_min'),1),
		);
		
		if(!$vservice->validation($validate,$data))
			exit($vservice->getError());
		
		if(!isset($_FANWE['cache']['albums']['category'][$data['cid']]))
			exit;

		if(!checkIpOperation("add_share",SHARE_INTERVAL_TIME))
		{
			showError('提交失败',lang('share','interval_tips'),-1);
		}
		
		$check_result = FS('Share')->checkWord($_FANWE['request']['title'],'title');
		if($check_result['error_code'] == 1)
		{
			showError('提交失败',$check_result['error_msg'],-1);
		}
		
		$check_result = FS('Share')->checkWord($_FANWE['request']['content'],'content');
		if($check_result['error_code'] == 1)
		{
			showError('提交失败',$check_result['error_msg'],-1);
		}

		$check_result = FS('Share')->checkWord($_FANWE['request']['tags'],'tag');
		if($check_result['error_code'] == 1)
		{
			showError('提交失败',$check_result['error_msg'],-1);
		}
		
		$tags = str_replace('***','',$_FANWE['request']['tags']);
		$tags = str_replace('　',' ',$tags);
		$tags = explode(' ',$tags);
		$tags = array_unique($tags);
		if(count($tags) > $_FANWE['cache']['albums']['setting']['album_tag_count']){
			exit;
		}
		
		$img = array();
		$img['type'] = 'album';
		$img['src'] = false;
		$imgId = $_FANWE['request']['imgId'];
		$albumImg = trim($_FANWE['request']['album_img']);
		if(!empty($albumImg) && file_exists(FANWE_ROOT.$albumImg)){
			$img['src'] = FANWE_ROOT.$albumImg;
		}
		
		if($img['src'] !== false){
			if($imgId > 0){
				$img['id'] = $imgId;
				FS('Image')->updateImage($img,true);
			}else{
				$img = FS('Image')->addImage($img);
			}
		}
		
		if($id > 0){
			$data['title'] = htmlspecialchars($_FANWE['request']['title']);
			$data['content'] = htmlspecialchars($_FANWE['request']['content']);
			$data['tags'] = implode(' ',$tags);
			if(empty($imgId)){
				$data['img'] = $img['id'];
			}
			FDB::update('album',$data,'id = '.$id);
			FS('Share')->updateShare($album['share_id'],$data['title'],$data['content']);
			FS("Album")->saveTags($id,$tags);
			
			$content_match = trim($data['title'].' '.$data['tags']);
			$content_match = FS('Words')->segmentToUnicode($content_match);
			FDB::insert("album_match",array('id'=>$id,'content'=>$content_match),false,true);
			
			if($data['cid'] != $album['cid'])
			{
				FDB::query('UPDATE '.FDB::table("album_share").' SET cid = '.$data['cid'].' WHERE album_id = '.$id);
				FDB::query('UPDATE '.FDB::table("album_share_index").' SET cid = '.$data['cid'].' WHERE album_id = '.$id);
			}
			$url = FU('album/show',array('id'=>$id));
			fHeader('location: '.$url);
			exit;
		}else{
			$_FANWE['request']['uid'] = $_FANWE['uid'];
			$_FANWE['request']['type'] = 'album';
			$share = FS('Share')->submit($_FANWE['request']);
			
			if($share['status'])
			{
				$data['title'] = htmlspecialchars($_FANWE['request']['title']);
				$data['content'] = htmlspecialchars($_FANWE['request']['content']);
				$data['tags'] = implode(' ',$tags);
				$data['uid'] = $_FANWE['uid'];
				$data['share_id'] = $share['share_id'];
				$data['create_day'] = getTodayTime();
				$data['create_time'] = TIME_UTC;
				$data['img'] = $img['id'];
				$aid = FDB::insert('album',$data,true);
					
				FS("Album")->saveTags($aid,$tags);
					
				$content_match = trim($data['title'].' '.$data['tags']);
				$content_match = FS('Words')->segmentToUnicode($content_match);
				FDB::insert("album_match",array('id'=>$aid,'content'=>$content_match),false,true);
					
				FDB::query('UPDATE '.FDB::table('share').' SET rec_id = '.$aid.'
				WHERE share_id = '.$share['share_id']);
				FDB::query("update ".FDB::table("user_count")." set albums = albums + 1 where uid = ".$_FANWE['uid']);
				FS('Medal')->runAuto($_FANWE['uid'],'albums');
					
				$url = FU('album/show',array('id'=>$aid));
				fHeader('location: '.$url);
			}
			else{
				showError('提交失败','添加数据失败',-1);
			}
		}
	}
}
?>