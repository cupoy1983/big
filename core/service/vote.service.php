<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

/**  
 * vote.service.php
 *
 * 投票服务
 *
 * @package service
 * @author awfigq <awfigq@qq.com>
 */
class VoteService
{
	public function getById($id,$is_static = true)
	{
		$id = (int)$id;
		if(!$id)
			return false;
		
		static $votes = array();
		if(isset($votes[$id]) && $is_static)
			return $votes[$id];
		
		$vote = FDB::fetchFirst('SELECT * FROM '.FDB::table('vote').' WHERE id = '.$id);
		$votes[$id] = $vote;
		return $votes[$id];
	}
	
	public function getOption($id)
	{
		$id = (int)$id;
		if(!$id)
			return false;
		
		$option = FDB::fetchFirst('SELECT * FROM '.FDB::table('vote_option').' WHERE id = '.$id);
		return $option;
	}
	
	public function getOptions($id,$total_num)
	{
		$id = (int)$id;
		if(!$id)
			return array();
		
		$total_num = (int)$total_num;
		$list = array();
		$res = FDB::query('SELECT * FROM '.FDB::table('vote_option').' WHERE vid = '.$id.' ORDER BY sort ASC');
		while($data = FDB::fetch($res))
		{
			if($data['num'] == 0)
				$data['bate'] = 0;
			else
				$data['bate'] = round(($data['num'] / $total_num) * 10000) / 100;
			
			$list[] = $data;
		}
		return $list;
	}
	
	public function getVotesByUid($uid,$num)
	{
		$uid = (int)$uid;
		$num = (int)$num;
		if(!$uid || !$num)
			return array();
		
		$list = array();
		$sql = 'SELECT * FROM '.FDB::table('vote').' WHERE uid = '.$uid.' 
			ORDER BY id DESC LIMIT 0,'.$num;
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$data['url'] = FU('vote/detail',array('id'=>$data['id']));
			$list[] = $data;
		}
		return $list;
	}
	
	public function getParticiVotesByUid($uid,$num)
	{
		$uid = (int)$uid;
		$num = (int)$num;
		if(!$uid || !$num)
			return array();
		
		$list = array();
		$sql = 'SELECT DISTINCT(v.id),v.title,v.num,v.users FROM '.FDB::table('vote_user').' AS vu 
			INNER JOIN '.FDB::table('vote').' AS v ON v.id = vu.vid 
			WHERE vu.uid = '.$uid.' ORDER BY v.id DESC LIMIT 0,'.$num;
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$data['url'] = FU('vote/detail',array('id'=>$data['id']));
			$list[] = $data;
		}
		return $list;
	}
	
	public function getHotVotes($num)
	{
		$num = (int)$num;
		if(!$num)
			return array();
		
		$list = array();
		$sql = 'SELECT * FROM '.FDB::table('vote').' ORDER BY users DESC,id DESC LIMIT 0,'.$num;
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$data['url'] = FU('vote/detail',array('id'=>$data['id']));
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
			FROM '.FDB::table('vote_post').' AS vp 
			INNER JOIN '.FDB::table('share').' AS s ON s.share_id = vp.share_id  
			WHERE vp.vid = '.$id.' ORDER BY vp.id DESC LIMIT '.$limit;
		
		$list = FDB::fetchAll($sql);
		return FS('Share')->getShareDetailList($list,true,false,true);
	}
	
	public function getUserIsVote($id,$uid)
	{
		$id = (int)$id;
		if(!$id)
			return false;
		
		$count = (int)FDB::resultFirst('SELECT COUNT(id) FROM '.FDB::table('vote_user').' WHERE vid = '.$id.' AND uid = '.$uid);
		return $count == 0;
	}
	
	public function getUsers($id,$num)
	{
		$id = (int)$id;
		$num = (int)$num;
		if(!$id || !$num)
			return array();
		
		$list = array();
		$sql = 'SELECT DISTINCT uid FROM '.FDB::table('vote_user').' WHERE vid = '.$id.' ORDER BY id DESC LIMIT 0,'.$num;
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$list[] = $data['uid'];
		}
		return $list;
	}
	
	public function optionSubmit($vid,$uid,$ids)
	{
		$vid = (int)$vid;
		$uid = (int)$uid;
		if(!$vid || !$uid)
			return false;
			
		if(!is_array($ids))
		{
			if(empty($ids))
				return false;
			
			$ids = array($ids);	
		}
		elseif(count($ids) == 0)
		{
			return false;
		}
		
		$ids = array_unique($ids);
		$num = 0;
		foreach($ids as $oid)
		{
			$oid = (int)$oid;
			if($oid > 0)
			{
				$option = VoteService::getOption($oid);
				if($option && $option['vid'] == $vid)
				{
					$ou = array();
					$ou['vid'] = $vid;
					$ou['oid'] = $oid;
					$ou['uid'] = $uid;
					$ou['create_time'] = TIME_UTC;
					if(FDB::insert('vote_user',$ou,true) > 0)
					{
						$num++;
						FDB::query('UPDATE '.FDB::table('vote_option').' SET num = num + 1 WHERE id = '.$oid);
					}
				}
			}
		}
		
		if($num > 0)
		{
			FDB::query('UPDATE '.FDB::table('vote').' SET users = users + 1,num = num + '.$num.' WHERE id = '.$vid);
			return true;
		}
		return false;
	}
	
	public function savePost($vid,$content,$share_id = 0)
	{
		global $_FANWE;
		$post = array();
		$post['vid'] = $vid;
		$post['share_id'] = $share_id;
		$post['uid'] = $_FANWE['uid'];
		$post['content'] = $content;
		$post['create_time'] = TIME_UTC;
		$id = FDB::insert('vote_post',$post,true);
		if($id > 0)
		{
			FDB::query("update ".FDB::table("user_count")." set vote_post = vote_post + 1 where uid = ".$_FANWE['uid']);
		}

		return $id;
	}
	
	public function deletePost($share_id,$is_score = true)
	{
		if(intval($share_id) == 0)
			return false;

		$post = FDB::fetchFirst('SELECT * FROM '.FDB::table('vote_post').' WHERE share_id = '.$share_id);
		if(empty($post))
			return true;

		FDB::delete('vote_post','share_id = '.$share_id);
		
		FDB::query('UPDATE '.FDB::table('user_count').' SET
				vote_post = vote_post - 1
				WHERE uid = '.$post['uid']);
				
		FS('Share')->deleteShare($share_id,$is_score);
		FS('Medal')->runAuto($post['uid'],'vote_post');
	}
	
	public function save($data)
	{
		global $_FANWE;
		$vote = array();
		$vote['share_id'] = $data['share_id'];
		$vote['title'] = htmlspecialchars(trim($data['title']));
		$vote['uid'] = $_FANWE['uid'];
		$vote['multiple'] = (int)$data['multiple'];
		$vote['visibility'] = (int)$data['visibility'];
		$vote['expiration_time'] = str2Time($data['expiration_time']);
		$vote['create_time'] = TIME_UTC;
		$vid = FDB::insert('vote',$vote,true);
		FDB::query("update ".FDB::table("user_count")." set vote = vote + 1 where uid = ".$_FANWE['uid']);
		if($vid > 0)
		{
			$content_match = trim($data['title']);
			$content_match = FS('Words')->segmentToUnicode($content_match);
			FDB::insert("vote_match",array('id'=>$vid,'content'=>$content_match));
			
			$vote_count = 0;
			foreach($data['vote_item'] as $vote_item)
			{
				if(!empty($vote_item))
				{
					$vote_count++;
					$option = array();
					$option['vid'] = $vid;
					$option['title'] = cutStr($vote_item,40,'');
					$option['sort'] = $vote_count;
					FDB::insert('vote_option',$option);
					if($vote_count >= 20)
						break;
				}
			}
		}
		return $vid;
	}
	
	public function update($id,$post)
	{
		global $_FANWE;
		$vote = VoteService::getById($id);
		
		$data['title'] = htmlspecialchars(trim($post['title']));
		$data['multiple'] = (int)$post['multiple'];
		$data['visibility'] = (int)$post['visibility'];
		$data['expiration_time'] = str2Time($post['expiration_time']);
		FDB::update('vote',$data,'id = '.$id);
		$content_match = trim($post['title']);
		$content_match = FS('Words')->segmentToUnicode($content_match);
		FDB::insert("vote_match",array('content'=>$content_match,'id'=>$id),false,true);
		FS("Share")->updateShare($vote['share_id'],$data['title'],htmlspecialchars(trim(cutStr($post['content'],1000,''))));
		
		foreach($post['vote_option'] as $oid => $vote_item)
		{
			$oid = (int)$oid;
			if(!empty($vote_item) && $oid > 0)
			{
				$option = array();
				$option['title'] = cutStr($vote_item,40,'');
				$option['sort'] = (int)$post['vote_osort'][$oid];
				FDB::update('vote_option',$option,'id = '.$oid);
				if(count($options) >= 20)
					break;
			}
		}
		
		foreach($post['vote_item'] as $key => $vote_item)
		{
			if(!empty($vote_item))
			{
				$option = array();
				$option['vid'] = $id;
				$option['title'] = cutStr($vote_item,40,'');
				$option['sort'] = (int)$post['vote_sort'][$key];
				FDB::insert('vote_option',$option);
				if(count($options) >= 20)
					break;
			}
		}
	}
	
	public function delete($id,$is_score = true)
	{
		global $_FANWE;
		$vote = VoteService::getById($id);
		if(empty($vote))
			return;
		
		setTimeLimit(600);

		$share_id = $vote['share_id'];
		$share = FS('Share')->getShareById($share_id);
		FS('Share')->deleteShare($share_id,$is_score);
		
		$res = FDB::query('SELECT * FROM '.FDB::table('vote_post').' WHERE vid = '.$id);
		while($data = FDB::fetch($res))
		{
            VoteService::deletePost($data['share_id'],false);
		}
		FDB::query('DELETE FROM '.FDB::table('vote_post').' WHERE vid = '.$id);
		FDB::query('DELETE FROM '.FDB::table('vote_user').' WHERE vid = '.$id);
		FDB::query('DELETE FROM '.FDB::table('vote_option').' WHERE vid = '.$id);
		FDB::query('DELETE FROM '.FDB::table('vote_match').' WHERE id = '.$id);
		FDB::query('DELETE FROM '.FDB::table('vote').' WHERE id = '.$id);

		FS('Medal')->runAuto($share['uid'],'vote');
	}
}
?>