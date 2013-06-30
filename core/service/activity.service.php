<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

/**  
 * activity.service.php
 *
 * 活动服务
 *
 * @package service
 * @author awfigq <awfigq@qq.com>
 */
class ActivityService
{
	public function getById($id,$is_static = true)
	{
		$id = (int)$id;
		if(!$id)
			return false;
		
		static $activitys = array();
		if(isset($activitys[$id]) && $is_static)
			return $activitys[$id];
		
		$activity = FDB::fetchFirst('SELECT * FROM '.FDB::table('activity').' WHERE id = '.$id);
		$activitys[$id] = $activity;
		return $activitys[$id];
	}
	
	public function getActivitysByUid($uid,$num)
	{
		$uid = (int)$uid;
		$num = (int)$num;
		if(!$uid || !$num)
			return array();
		
		$list = array();
		$sql = 'SELECT * FROM '.FDB::table('activity').' WHERE uid = '.$uid.' 
			ORDER BY id DESC LIMIT 0,'.$num;
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$data['url'] = FU('activity/detail',array('id'=>$data['id']));
			$list[] = $data;
		}
		return $list;
	}
	
	public function getParticiActivitysByUid($uid,$num)
	{
		$uid = (int)$uid;
		$num = (int)$num;
		if(!$uid || !$num)
			return array();
		
		$list = array();
		$sql = 'SELECT DISTINCT(a.id),a.title,a.apply_number FROM '.FDB::table('activity_apply').' AS ap 
			INNER JOIN '.FDB::table('activity').' AS a ON a.id = ap.aid 
			WHERE ap.uid = '.$uid.' ORDER BY a.id DESC LIMIT 0,'.$num;
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$data['url'] = FU('activity/detail',array('id'=>$data['id']));
			$list[] = $data;
		}
		return $list;
	}
	
	public function getHotActivitys($num)
	{
		$num = (int)$num;
		if(!$num)
			return array();
		
		$list = array();
		$sql = 'SELECT * FROM '.FDB::table('activity').' ORDER BY apply_number DESC,id DESC LIMIT 0,'.$num;
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$data['url'] = FU('activity/detail',array('id'=>$data['id']));
			$list[] = $data;
		}
		return $list;
	}
	
	public function getPostList($id,$limit)
	{
		$id = (int)$id;
		if(!$id)
			return array();
		
		$sql = 'SELECT s.* 
			FROM '.FDB::table('activity_post').' AS ap 
			INNER JOIN '.FDB::table('share').' AS s ON s.share_id = ap.share_id  
			WHERE ap.aid = '.$id.' ORDER BY ap.id DESC LIMIT '.$limit;
		
		$list = FDB::fetchAll($sql);
		return FS('Share')->getShareDetailList($list,true,false,true);
	}
	
	public function getUserIsActivity($id,$uid)
	{
		$id = (int)$id;
		if(!$id)
			return -1;
		
		$apply = FDB::fetchFirst('SELECT status FROM '.FDB::table('activity_apply').' WHERE aid = '.$id.' AND uid = '.$uid);
		if($apply)
			return $apply['status'];
		else
			return -1;
	}
	
	public function getUserCount($id)
	{
		$id = (int)$id;
		if(!$id)
			return 0;
		return (int)FDB::resultFirst('SELECT COUNT(id) FROM '.FDB::table('activity_apply').' WHERE aid = '.$id.' AND status = 1');
	}
	
	public function getUsers($id,$limit)
	{
		$id = (int)$id;
		if(!$id)
			return array();
			
		if(empty($limit))
			$limit = '0,15';
		
		$list = array();
		$sql = 'SELECT uid FROM '.FDB::table('activity_apply').' 
			WHERE aid = '.$id.' AND status = 1 ORDER BY id DESC LIMIT '.$limit;

		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$list[] = $data['uid'];
		}
		return $list;
	}
	
	public function apply($aid,$uid,$uname,$data)
	{
		$data['aid'] = $aid;
		$data['uid'] = $uid;
		$data['create_time'] = TIME_UTC;
		$activity = ActivityService::getById($aid);
		if($activity['uid'] == $uid)
			$data['status'] = 1;
		
		$id = FDB::insert('activity_apply',$data,true);
		if($activity['uid'] != $uid)
		{
			$msg = sprintf(lang('activity','user_join_apply_content'),
				FU('u/index',array('uid'=>$uid)),
				$uname,
				$activity['title'],
				FU('activity/detail',array('id'=>$aid)));
			ActivityService::notice($activity['uid'],$msg);
		}
		else
		{
			FDB::query("UPDATE ".FDB::table("activity")." SET apply_number = apply_number + 1 WHERE id = ".$aid);
		}
		return $id;
	}
	
	public function clearApply($aid,$uid,$uname,$cmessage)
	{
		$apply = FDB::fetchFirst('SELECT status FROM '.FDB::table('activity_apply').' WHERE aid = '.$aid.' AND uid = '.$uid);
		if($apply)
		{
			FDB::delete('activity_apply','aid = '.$aid.' AND uid = '.$uid);
			$activity = ActivityService::getById($aid);
			if($activity['uid'] != $uid)
			{
				$msg = sprintf(lang('activity','user_join_clear_content'),
					FU('u/index',array('uid'=>$uid)),
					$uname,
					FU('activity/detail',array('id'=>$aid)),
					$activity['title']);
					
				if(!empty($cmessage))
					$msg .= sprintf(lang('activity','user_join_clear_yy'),$cmessage);
				ActivityService::notice($activity['uid'],$msg);
			}
			
			if($apply['status'] == 1)
			{
				FDB::query("UPDATE ".FDB::table("activity")." SET apply_number = apply_number - 1 WHERE id = ".$aid);
			}
		}
	}
	
	public function manage($aid,$ids,$status,$message)
	{
		$activity = ActivityService::getById($aid);
		$notice['title'] = lang('activity','activity_notice');
		foreach($ids as $id)
		{
			$id = (int)$id;
			if($id > 0)
			{
				$apply = FDB::fetchFirst('SELECT * FROM '.FDB::table('activity_apply').' WHERE id = '.$id.' AND aid = '.$aid);
				if($apply)
				{
					switch($status)
					{
						case 0:
							if($apply['status'] != 0)
							{
								FDB::delete('activity_apply','id = '.$id);
								if($apply['status'] == 1)
									FDB::query("UPDATE ".FDB::table("activity")." SET apply_number = apply_number - 1 WHERE id = ".$aid);
								if($activity['uid'] != $apply['uid'])
								{
									$notice['uid'] = $apply['uid'];
									$msg = sprintf(lang('activity','user_join_apply_failure'),
										FU('activity/detail',array('id'=>$aid)),
										$activity['title']);
									
									if(!empty($message))
										$msg .= sprintf(lang('activity','user_join_apply_failure_yy'),$message);
									
									$notice['content'] = $msg;
									FS('Notice')->send($notice);
								}
							}
						break;
						
						case 1:
							if($apply['status'] != 1)
							{
								FDB::query("UPDATE ".FDB::table("activity_apply")." SET status = 1 WHERE id = ".$id);
								FDB::query("UPDATE ".FDB::table("activity")." SET apply_number = apply_number + 1 WHERE id = ".$aid);
								if($activity['uid'] != $apply['uid'])
								{
									$notice['uid'] = $apply['uid'];
									$msg = sprintf(lang('activity','user_join_apply_success'),
										FU('activity/detail',array('id'=>$aid)),
										$activity['title']);
									
									if(!empty($message))
										$msg .= sprintf(lang('activity','user_join_apply_success_yy'),$message);
									
									$notice['content'] = $msg;
									FS('Notice')->send($notice);
				
									$share = array();
									$share['share']['uid'] = $apply['uid'];
									$share['share']['rec_id'] = $aid;
									$share['share']['content'] = sprintf(lang('activity','user_join_apply_share'),
										FU('activity/detail',array('id'=>$aid)),
										$activity['title']);
									FS('Share')->save($share);
								}
							}
						break;
						
						case -1:
							if($activity['uid'] != $apply['uid'])
							{
								$notice['uid'] = $apply['uid'];
								$msg = sprintf(lang('activity','activity_user_notice'),
									FU('activity/detail',array('id'=>$aid)),
									$activity['title'],
									$message);
								
								$notice['content'] = $msg;
								FS('Notice')->send($notice);
							}
						break;
					}
				}
			}
		}
	}
	
	public function notice($uid,$msg)
	{
		$notice = array();
		$notice['uid'] = $uid;
		$notice['title'] = lang('activity','activity_notice');
		$notice['content'] = $msg;
		FS('Notice')->send($notice);
	}
	
	public function savePost($aid,$content,$share_id = 0)
	{
		global $_FANWE;
		$post = array();
		$post['aid'] = $aid;
		$post['share_id'] = $share_id;
		$post['uid'] = $_FANWE['uid'];
		$post['content'] = $content;
		$post['create_time'] = TIME_UTC;
		$id = FDB::insert('activity_post',$post,true);
		if($id > 0)
		{
			FDB::query("update ".FDB::table("user_count")." set activity_post = activity_post + 1 where uid = ".$_FANWE['uid']);
		}

		return $id;
	}
	
	public function deletePost($share_id,$is_score = true)
	{
		if(intval($share_id) == 0)
			return false;

		$post = FDB::fetchFirst('SELECT * FROM '.FDB::table('activity_post').' WHERE share_id = '.$share_id);
		if(empty($post))
			return true;

		FDB::delete('activity_post','share_id = '.$share_id);
		
		FDB::query('UPDATE '.FDB::table('user_count').' SET
				activity_post = activity_post - 1
				WHERE uid = '.$post['uid']);
				
		FS('Share')->deleteShare($share_id,$is_score);
		FS('Medal')->runAuto($post['uid'],'activity_post');
	}
	
	public function save($data)
	{
		global $_FANWE;
		$activity = array();
		$activity['share_id'] = $data['share_id'];
		$activity['title'] = htmlspecialchars(trim($data['title']));
		$activity['uid'] = $_FANWE['uid'];
		$activity['cid'] = (int)$data['cid'];
		$activity['cost'] = (float)$data['cost'];
		$activity['begin_time'] = str2Time($data['begin_time']);
		if((int)$data['time_range'] > 0)
			$activity['end_time'] = str2Time($data['end_time']);
		else
			$activity['end_time'] = 0;
		
		$activity['place'] = htmlspecialchars(trim($data['place']));		
		$activity['gender'] = (int)$data['gender'];
		$activity['number'] = (int)$data['number'];
		$activity['expiration_time'] = str2Time($data['expiration_time']);
		$activity['fields'] = trim($data['fields']);
		if(!empty($activity['fields']))
		{
			$temps = array();
			$activity['fields'] = explode("\n",$activity['fields']);
			foreach($activity['fields'] as $field)
			{
				$field = trim($field);
				if(!empty($field))
				{
					$temps[] = $field;
					if(count($temps) >= 10)
						break;
				}
			}
			$activity['fields'] = implode("\n",$temps);
			unset($temps);
		}
		
		$activity['province'] = (int)$data['province'];
		$activity['create_time'] = TIME_UTC;
		$id = FDB::insert('activity',$activity,true);
		if($id > 0)
		{
			FDB::query("update ".FDB::table("user_count")." set activity = activity + 1 where uid = ".$_FANWE['uid']);
			$content_match = trim($data['title']);
			$content_match = FS('Words')->segmentToUnicode($content_match);
			FDB::insert("activity_match",array('id'=>$id,'content'=>$content_match));
		}
		return $id;
	}
	
	public function update($id,$post)
	{
		global $_FANWE;
		$activity = ActivityService::getById($id);
		
		$data['title'] = htmlspecialchars(trim($post['title']));
		$data['cid'] = (int)$post['cid'];
		$data['cost'] = (float)$post['cost'];
		$data['begin_time'] = str2Time($post['begin_time']);
		if((int)$post['time_range'] > 0)
			$data['end_time'] = str2Time($post['end_time']);
		else
			$data['end_time'] = 0;
		
		$data['place'] = htmlspecialchars(trim($post['place']));		
		$data['gender'] = (int)$post['gender'];
		$data['number'] = (int)$post['number'];
		$data['expiration_time'] = str2Time($post['expiration_time']);
		$data['province'] = (int)$post['province'];
		
		$data['fields'] = array();
		foreach($post['old_fields'] as $field)
		{
			$field = trim($field);
			if(!empty($field))
			{
				$data['fields'][] = $field;
				if(count($data['fields']) >= 10)
					break;
			}
		}
		
		if(!empty($post['fields']))
		{
			$post['fields'] = explode("\n",$post['fields']);
			foreach($post['fields'] as $field)
			{
				$field = trim($field);
				if(!empty($field))
				{
					$data['fields'][] = $field;
					if(count($data['fields']) >= 10)
						break;
				}
			}
		}
		$data['fields'] = implode("\n",$data['fields']);
		
		FDB::update('activity',$data,'id = '.$id);
		$content_match = trim($post['title']);
		$content_match = FS('Words')->segmentToUnicode($content_match);
		FDB::insert("activity_match",array('content'=>$content_match,'id'=>$id),false,true);
		FS("Share")->updateShare($activity['share_id'],$data['title'],htmlspecialchars(trim(cutStr($post['content'],1000,''))));
	}
	
	public function delete($id,$is_score = true)
	{
		global $_FANWE;
		$activity = ActivityService::getById($id);
		if(empty($activity))
			return;
		
		setTimeLimit(600);

		$share_id = $activity['share_id'];
		$share = FS('Share')->getShareById($share_id);
		FS('Share')->deleteShare($share_id,$is_score);
		
		$res = FDB::query('SELECT * FROM '.FDB::table('activity_post').' WHERE aid = '.$id);
		while($data = FDB::fetch($res))
		{
            ActivityService::deletePost($data['share_id'],false);
		}
		FDB::query('DELETE FROM '.FDB::table('activity_post').' WHERE aid = '.$id);
		FDB::query('DELETE FROM '.FDB::table('activity_apply').' WHERE aid = '.$id);
		FDB::query('DELETE FROM '.FDB::table('activity_match').' WHERE id = '.$id);
		FDB::query('DELETE FROM '.FDB::table('activity').' WHERE id = '.$id);

		FS('Medal')->runAuto($share['uid'],'activity');
	}
}
?>