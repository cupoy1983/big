<?php
class ActivityModule
{
	public function detail()
	{
		global $_FANWE;
		$id = (int)$_FANWE['request']['id'];
		$activity = FS('Activity')->getById($id);
		
		if(empty($activity))
			fHeader('location: '.FU('index/index'));

		$activity['time'] = getBeforeTimelag($activity['create_time']);
		if($activity['expiration_time'] > 0)
			$activity['expiration_date'] = fToDate($activity['expiration_time'],'Y-m-d H:i');
		
		if($activity['end_time'] == 0)
			$activity['begin_date'] = fToDate($activity['begin_time'],'Y-m-d');
		else
		{
			$activity['begin_date'] = fToDate($activity['begin_time'],'Y-m-d H:i');
			$activity['end_date'] = fToDate($activity['end_time'],'Y-m-d H:i');
		}
		
		$activity['fields'] = explode("\n",trim($activity['fields']));
		$activity['city'] = $_FANWE['cache']['citys']['all'][$activity['province']]['name'];
		$activity['cate_name'] = $_FANWE['cache']['activity_cate'][$activity['cid']]['name'];
		if($activity['cost'] > 0)
			$activity['cost_format'] = priceFormat($activity['cost']);
		
		$activity['over_num'] = $activity['number'] - $activity['apply_number'];
		$activity['share'] = FS('Share')->getShareDetail($activity['share_id']);
		$activity['content'] = $activity['share']['content'];
		$activity['share_content'] = str_replace(array('#','@',"\r","\n"),array('＃','＠',' ',' '),$activity['share']['content']);
		$activity['img'] = false;
		if(count($activity['share']['imgs']) > 0)
		{
			$activity['img'] = $activity['share']['imgs'][0];
			unset($activity['share']['imgs'][0]);
		}
		
		$activity_user = FS('User')->getUserShowName($activity['uid']);
		$activity_users_count = FS('Activity')->getUserCount($id);
		$activity_users_pager = buildPageMini($activity_users_count,1,15);
		$activity_users = FS('Activity')->getUsers($id,$activity_users_pager['limit']);
		
		$user_activitys = FS('Activity')->getActivitysByUid($activity['uid'],9);
		$partici_activitys = FS('Activity')->getParticiActivitysByUid($activity['uid'],9);
		$hot_activitys = FS('Activity')->getHotActivitys(9);
		
		$apply_status = -1;
		if($_FANWE['uid'] > 0)
			$apply_status = FS('Activity')->getUserIsActivity($id,$_FANWE['uid']);
		
		$_FANWE['nav_title'] = $activity['title'].'-'.lang('common','activity');
		
		$page_args = array(
			'id'=>$id
		);

		$count = (int)FDB::resultFirst('SELECT COUNT(id) FROM '.FDB::table('activity_post').' WHERE aid = '.$id);
		
		$pager = buildPage('activity/detail',$page_args,$count,$_FANWE['page'],10);
		$post_list = FS('Activity')->getPostList($id,$pager['limit']);
		
		$args = array(
			'share_list'=>&$post_list,
			'pager'=>&$pager,
			'current_share_id'=>$activity['share_id']
		);
		$post_html = tplFetch("inc/share/post_share_list",$args);

		include template('page/activity/activity_detail');
		display();
	}
	
	public function manage()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
			fHeader('location: '.FU('index/index'));
		
		$id= (int)$_FANWE['request']['id'];
		if($id == 0)
			fHeader('location: '.FU('index/index'));
		
		$activity = FS('Activity')->getById($id);
		
		if(empty($activity))
			fHeader('location: '.FU('index/index'));
		
		if($activity['uid'] != $_FANWE['uid'])
			fHeader('location: '.FU('index/index'));
		
		$_FANWE['nav_title'] = lang('common','activity');
		
		$status = (int)$_FANWE['request']['status'];
		$where = '';
		if($status > 0)
		{
			$where .= ' AND ap.status = '.($status - 1);
		}
		
		$apply_count = (int)FDB::resultFirst('SELECT COUNT(ap.id) FROM '.FDB::table('activity_apply').' AS ap WHERE ap.aid = '.$id.$where);
		$pager = buildPage('activity/manage',array('id'=>$id),$apply_count,$_FANWE['page'],20);
		$apply_list = array();
		$res = FDB::query('SELECT ap.*,u.user_name FROM '.FDB::table('activity_apply').' AS ap 
			LEFT JOIN '.FDB::table('user').' AS u ON u.uid = ap.uid  
			WHERE ap.aid = '.$id.$where.' ORDER BY ap.id DESC LIMIT '.$pager['limit']);
		while($data = FDB::fetch($res))
		{
			$data['time'] = fToDate($data['create_time']);
			$data['fields_data'] = unserialize($data['fields_data']);
			$apply_list[] = $data;
		}
		include template('page/activity/activity_manage');
		display();
	}
	
	public function export()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
			fHeader('location: '.FU('index/index'));
		
		$id= (int)$_FANWE['request']['id'];
		if($id == 0)
			fHeader('location: '.FU('index/index'));
		
		$activity = FS('Activity')->getById($id);
		
		if(empty($activity))
			fHeader('location: '.FU('index/index'));
		
		if($activity['uid'] != $_FANWE['uid'])
			fHeader('location: '.FU('index/index'));
			
		$activity['time'] = fToDate($activity['create_time'],'Y-m-d');
		if($activity['expiration_time'] > 0)
			$activity['expiration_date'] = fToDate($activity['expiration_time'],'Y-m-d H:i');
		
		if($activity['end_time'] == 0)
			$activity['begin_date'] = fToDate($activity['begin_time'],'Y-m-d');
		else
		{
			$activity['begin_date'] = fToDate($activity['begin_time'],'Y-m-d H:i');
			$activity['end_date'] = fToDate($activity['end_time'],'Y-m-d H:i');
		}
		
		$activity['share'] = FS('Share')->getShareDetail($activity['share_id']);
		$activity['content'] = $activity['share']['content'];
		$activity['city'] = $_FANWE['cache']['citys']['all'][$activity['province']]['name'];
		$activity['cate_name'] = $_FANWE['cache']['activity_cate'][$activity['cid']]['name'];
		if($activity['cost'] > 0)
			$activity['cost_format'] = priceFormat($activity['cost']);
		
		$field_names = '';
		$fields = explode("\n",trim($activity['fields']));
		foreach($fields as $field)
		{
			$field_names .= ','.str_replace(',','，',$field);
		}
		
		$apply_list = array();
		$res = FDB::query('SELECT ap.*,u.user_name FROM '.FDB::table('activity_apply').' AS ap 
			LEFT JOIN '.FDB::table('user').' AS u ON u.uid = ap.uid  
			WHERE ap.aid = '.$id.' ORDER BY ap.id DESC');
		while($data = FDB::fetch($res))
		{
			$data['time'] = fToDate($data['create_time']);
			$field_values = unserialize($data['fields_data']);
			$data['field_values'] = '';
			foreach($fields as $field)
			{
				$val = $field_values[$field];
				$data['field_values'] .= ','.str_replace(',','，',$val);
			}
			$apply_list[] = $data;
		}
		$filename = "activity_".$id.".csv";
		include template('page/activity/activity_export');
		$csvstr = ob_get_contents();
		ob_end_clean();
		header('Content-Encoding: none');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Pragma: no-cache');
		header('Expires: 0');
		$csvstr = utf8ToGB($csvstr);
		echo $csvstr;
	}
	
	public function edit()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
			fHeader('location: '.FU('index/index'));
			
		$id= (int)$_FANWE['request']['id'];
		if($id == 0)
			fHeader('location: '.FU('index/index'));
			
		$activity = FS('Activity')->getById($id);
		if(empty($activity))
			fHeader('location: '.FU('index/index'));
		
		if($activity['uid'] != $_FANWE['uid'])
			fHeader('location: '.FU('index/index'));
			
		$activity['expiration_date'] = '';
		if($activity['expiration_time'] > 0)
			$activity['expiration_date'] = fToDate($activity['expiration_time'],'Y-m-d H:i');
		
		$activity['end_date'] = '';
		if($activity['end_time'] == 0)
			$activity['begin_date'] = fToDate($activity['begin_time'],'Y-m-d');
		else
		{
			$activity['begin_date'] = fToDate($activity['begin_time'],'Y-m-d H:i');
			$activity['end_date'] = fToDate($activity['end_time'],'Y-m-d H:i');
		}
		
		$activity['share'] = FS('Share')->getShareDetail($activity['share_id']);
		$activity_fields = explode("\n",trim($activity['fields']));
		$fields_count = 10 - count($activity_fields);
		
		$_FANWE['nav_title'] = lang('common','vote');
		include template('page/activity/activity_edit');
		display();
	}
	
	public function update()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
			fHeader('location: '.FU('index/index'));
			
		$id= (int)$_FANWE['request']['id'];
		if($id == 0)
			fHeader('location: '.FU('index/index'));
			
		$activity = FS('Activity')->getById($id);
		if(empty($activity))
			fHeader('location: '.FU('index/index'));
		
		if($activity['uid'] != $_FANWE['uid'])
			fHeader('location: '.FU('index/index'));
		
		FS("Activity")->update($id,$_FANWE['request']);
		fHeader('Location: '.FU('activity/detail',array('id'=>$id)));
	}
}
?>