<?php
class DarenModule
{
	public function index()
	{			
		global $_FANWE;
		$cache_file = getTplCache('page/daren',$_FANWE['request'],2);
		//FIXME getCacheIsUpdate($cache_file,SHARE_CACHE_TIME,1)
		if(true)
		{
			$_FANWE['nav_title'] = lang('common','daren');
			$filter = FS('Daren')->getDarensByType(1,10);
			$new_darens = FS('Daren')->getNewDarens(5);
			$top_darens = FS('Daren')->getTopDarens(9);
			foreach ($filter as $k => $v){
				$filter[$k]["dapei"] = FS("Dapei")->getUserDapeiImage($v["uid"],8);
			}
			include template('page/daren');
			display($cache_file); 
			exit;
		}
		else
		{
			include $cache_file;
			display();
		}
	}

	public function look()
	{			
		DarenModule::showList('look');
	}

	public function dapei()
	{			
		DarenModule::showList('dapei');
	}

	public function group()
	{			
		DarenModule::showList('group');
	}

	public function album()
	{			
		DarenModule::showList('album');
	}
	
	public function showList($daren_type)
	{			
		global $_FANWE;
		$is_best = false;
		$is_all = true;
		Cache::getInstance()->loadCache(array('daren_cate','citys'));
		$daren_cate = '';
		switch($daren_type)
		{
			case 'look':
				$daren_cate = $_FANWE['cache']['daren_cate'][1];
			break;
			case 'dapei':
				$daren_cate = $_FANWE['cache']['daren_cate'][2];
			break;
			case 'group':
				$daren_cate = $_FANWE['cache']['daren_cate'][4];
			break;
			case 'album':
				$daren_cate = $_FANWE['cache']['daren_cate'][3];
			break;
		}
		
		$_FANWE['PAGE_SEO_SELF'] = $daren_cate;
		
		$count = FDB::resultFirst('SELECT COUNT(udc.uid) FROM '.FDB::table('user_daren_cate').' AS udc 
			INNER JOIN '.FDB::table('user_daren').' AS ud ON ud.id = udc.id AND ud.status = 1 
			WHERE udc.cid = '.$daren_cate['id']);
		
		$pager = buildPage('daren/'.$daren_type,array(),$count,$_FANWE['page'],20);
		
		$daren_list = array();
		$user_ids = array();
		$sql = 'SELECT ud.*,udc.content  
			FROM '.FDB::table('user_daren_cate').' AS udc 
			INNER JOIN '.FDB::table('user_daren').' AS ud ON ud.id = udc.id AND ud.status = 1 
			WHERE udc.cid = '.$daren_cate['id'].' ORDER BY ud.sort ASC,ud.id DESC LIMIT '.$pager['limit'];
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			//$cache_data = fStripslashes(unserialize($data['cache_data']));
			//$data['cache_data'] = $cache_data;
			$daren_list[$data['uid']] = $data;
			$user_ids[$data['uid']] = array();
			$daren_list[$data['uid']]['user'] = &$user_ids[$data['uid']];
		}

		if(count($user_ids) > 0)
		{
			$sql = 'SELECT uid,cache_data  
				FROM '.FDB::table('user_status').' 
				WHERE uid IN ('.implode(',',array_keys($user_ids)).')';
			$res = FDB::query($sql);
			while($data = FDB::fetch($res))
			{
				$cache_data = fStripslashes(unserialize($data['cache_data']));
				$daren_list[$data['uid']]['cache_data'] = $cache_data;
			}

			$sql = 'SELECT uid,reside_province,reside_city 
				FROM '.FDB::table('user_profile').' 
				WHERE uid IN ('.implode(',',array_keys($user_ids)).')';
			$res = FDB::query($sql);
			while($data = FDB::fetch($res))
			{
				$province = $_FANWE['cache']['citys']['all'][$data['reside_province']]['name'];
				$city = $_FANWE['cache']['citys']['all'][$data['reside_city']]['name'];
				$daren_list[$data['uid']]['city'] = $province.'&nbsp;'.$city;
			}
			FS('User')->usersFormat($user_ids);
		}

		include template('page/daren/daren_type');		
		display();
	}
	
	public function apply()
	{
		global $_FANWE;
		include template('page/daren/daren_apply');		
		display();
	}
	
	public function save()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
			exit;
		
		include_once fimport('class/image');
		$image = new Image();
		if(intval($_FANWE['setting']['max_upload']) > 0)
			$image->max_size = intval($_FANWE['setting']['max_upload']);
		
		$daren = array();
		$daren['uid'] = $_FANWE['uid'];
		$daren['reason'] = $_FANWE['request']['reason'];
		$daren['status'] = 0;
		$daren['create_time'] = TIME_UTC;
		
		//个人街拍照
		$img = $_FILES['img'];
		if(!empty($img))
		{
			$image->init($img,'daren');
			if($image->save())
				$daren['img'] = $image->file['target'];
		}
		
		$index_img = $_FILES['index_img'];
		if(!empty($index_img))
		{
			$image->init($index_img,'daren');
			if($image->save())
				$daren['index_img'] = $image->file['target'];
		}
		
		$id = FDB::insert('user_daren',$daren,true,false,true);
		if($id > 0)
			showSuccess('提交申请成功','你的选款师申请已经成功提交，我们会尽快处理你的选款师申请！',FU('daren/index'));
		else
			showError('提交申请失败','你的选款师申请提交失败，请重新提交选款师申请',-1);
	}
}
?>