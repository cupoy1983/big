<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

/**  
 * exchange.service.php
 *
 * 积分兑换服务
 *
 * @package service
 * @author awfigq <awfigq@qq.com>
 */
class ExchangeService
{
	public function getById($id,$is_static = true)
	{
		$id = (int)$id;
		if(!$id)
			return false;
		
		static $exchanges = array();
		if(isset($exchanges[$id]) && $is_static)
			return $exchanges[$id];
		
		$exchange = FDB::fetchFirst('SELECT * FROM '.FDB::table('exchange_goods').' WHERE id = '.$id);
		$exchanges[$id] = $exchange;
		return $exchanges[$id];
	}
	
	/**
	 * 最新申请
	*/
	public function getApplyTop($num = 5)
	{
		$sql = 'SELECT o.uid,u.user_name,u.avatar,o.create_time,eg.id,eg.name  
				FROM '.FDB::table('order').' AS o 
				INNER JOIN '.FDB::table('exchange_goods').' AS eg ON eg.id = o.rec_id 
				INNER JOIN '.FDB::table('user').' AS u ON u.uid = o.uid 
				WHERE o.order_type = 1 
				ORDER BY o.id DESC LIMIT 0,'.$num;
		
		$list = array();
		$query = FDB::query($sql);
		while($data = FDB::fetch($query))
		{
			$data['create_time_format']  = fToDate($data['create_time'],'H:i:s');
			$list[] = $data;
		}
		
		return $list;
	}
	
	/**
	 * 最新兑换
	*/
	public function getOrderTop($num = 5)
	{
		$sql = 'SELECT o.uid,u.user_name,u.avatar,o.create_time,o.data_num,eg.id,eg.name   
				FROM '.FDB::table('order').' AS o 
				INNER JOIN '.FDB::table('exchange_goods').' AS eg ON eg.id = o.rec_id 
				INNER JOIN '.FDB::table('user').' AS u ON u.uid = o.uid 
				WHERE o.order_type = 0 
				ORDER BY o.id DESC LIMIT 0,'.$num;
		
		$list = array();
		$query = FDB::query($sql);
		while($data = FDB::fetch($query))
		{
			$data['create_time_format']  = fToDate($data['create_time'],'H:i:s');
			$list[] = $data;
		}
		
		return $list;
	}
	
	/**
	 * 积分排行
	*/
	public function getScoreTop($num = 10)
	{
		$sql = 'SELECT uid,user_name,avatar,credits FROM '.FDB::table('user').' 
				WHERE status = 1 ORDER BY credits DESC LIMIT 0,'.$num;
		
		$list = array();
		$query = FDB::query($sql);
		while($data = FDB::fetch($query))
		{
			$list[] = $data;
		}
		
		return $list;
	}
	
	/**
	 * 兑换排行
	*/
	public function getExchangeTop($num = 10)
	{
		$sql = "SELECT o.uid,o.user_name,SUM(o.data_num) AS sum_count ".
			    'FROM '.FDB::table('order').' AS o 
				WHERE o.order_type = 0 '.
				"GROUP BY o.uid ORDER BY sum_count DESC LIMIT 0,$num";
		
		$list = array();
		$query = FDB::query($sql);
		while($data = FDB::fetch($query))
		{
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
			FROM '.FDB::table('exchange_post').' AS ep 
			INNER JOIN '.FDB::table('share').' AS s ON s.share_id = ep.share_id  
			WHERE ep.eid = '.$id.' ORDER BY ep.id DESC LIMIT '.$limit;
		
		$list = FDB::fetchAll($sql);
		return FS('Share')->getShareDetailList($list,true,false,true);
	}
	
	public function setApplyCache($id)
	{
		$sql = 'SELECT o.uid,u.user_name,u.avatar 
				FROM '.FDB::table('order').' AS o 
				INNER JOIN '.FDB::table('user').' AS u ON u.uid = o.uid 
				WHERE o.rec_id = '.$id.' 
				ORDER BY o.id DESC LIMIT 0,20';
		
		$list = array();
		$query = FDB::query($sql);
		while($data = FDB::fetch($query))
		{
			$list[] = $data;
		}
		
		$data = array();
		$data['apply_cache'] = addslashes(serialize($list));
		FDB::update('exchange_goods',$data,'id = '.$id);
	}
	
	public function deletePost($share_id,$is_score = true)
	{
		if(intval($share_id) == 0)
			return false;

		$post = FDB::fetchFirst('SELECT * FROM '.FDB::table('exchange_post').' WHERE share_id = '.$share_id);
		if(empty($post))
			return true;

		FDB::delete('exchange_post','share_id = '.$share_id);
		
		FDB::query('UPDATE '.FDB::table('user_count').' SET
				trial_post = trial_post - 1
				WHERE uid = '.$post['uid']);
				
		FS('Share')->deleteShare($share_id,$is_score);
		FS('Medal')->runAuto($post['uid'],'trial_post');
	}
	
	public function delete($id,$is_score = true)
	{
		global $_FANWE;
		$exchange = ExchangeService::getById($id);
		
		if(empty($exchange))
			return;
		
		setTimeLimit(600);

		$share_id = $exchange['share_id'];
		if($share_id > 0)
		{
			$share = FS('Share')->getShareById($share_id);
			FS('Share')->deleteShare($share_id,false);
			
			$res = FDB::query('SELECT * FROM '.FDB::table('exchange_post').' WHERE eid = '.$id);
			while($data = FDB::fetch($res))
			{
				ExchangeService::deletePost($data['share_id'],false);
			}
		}
		FDB::query('DELETE FROM '.FDB::table('exchange_post').' WHERE eid = '.$id);
		FDB::query('DELETE FROM '.FDB::table('exchange_goods').' WHERE id = '.$id);

		FS('Medal')->runAuto($share['uid'],'trial');
	}
}
?>