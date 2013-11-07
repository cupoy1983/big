<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

/**  
 * dapei.service.php
 *
 * 搭配服务
 *
 * @package service
 * @author awfigq <awfigq@qq.com>
 */
class DapeiService
{
	public function setUserCache($uid)
	{
		$uid = (int)$uid;
		if($uid == 0)
			return;

		$user_cache = FDB::resultFirst('SELECT cache_data FROM '.FDB::table('user_status').' WHERE uid = '.$uid);
		$cache_data = fStripslashes(unserialize($user_cache));

		$dapeis = array();
		$sql = 'SELECT si.share_id,s.cache_data FROM '.FDB::table('share_dapei_index').' AS si 
			INNER JOIN '.FDB::table('share').' AS s ON s.share_id = si.share_id 
			WHERE si.uid = '.$uid.' ORDER BY si.share_id DESC LIMIT 0,10';
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$share_cache = fStripslashes(unserialize($data['cache_data']));
			$dapeis[] = array(
				'share_id'=>$data['share_id'],
				'id'=>$share_cache['imgs']['all'][$share_cache['imgs']['dapei'][0]]['id'],
				'img_id'=>$share_cache['imgs']['all'][$share_cache['imgs']['dapei'][0]]['img_id'],
				'img_width'=>$share_cache['imgs']['all'][$share_cache['imgs']['dapei'][0]]['img_width'],
				'img_height'=>$share_cache['imgs']['all'][$share_cache['imgs']['dapei'][0]]['img_height'],
			);
		}
		$cache_data['dapeis'] = $dapeis;
		$cache_data = addslashes(serialize($cache_data));
		FDB::update('user_status',array('cache_data'=>$cache_data),'uid = '.$uid);
	}

	public function getBest($num = 4)
	{
		$key = 'dapei/best/'.$num;
		$list = getCache($key);
		if($list === NULL)
		{
			$list = array();
			$sql = 'SELECT si.share_id,s.best_desc,s.content,s.uid,s.cache_data FROM '.FDB::table('share_dapei_best').' AS si 
				INNER JOIN '.FDB::table('share').' AS s ON s.share_id = si.share_id 
				ORDER BY s.sort ASC,si.share_id DESC LIMIT 0,'.$num;
			$res = FDB::query($sql);
			while($data = FDB::fetch($res))
			{
				$list[$data['share_id']] = $data;
			}
			$list = FS('Share')->getShareDetailList($list,false,false,false,true,2);
		}
		return $list;
	}
	
	/**
	 * 获取用户搭配的图片(第一张)
	 * @param $uid 用户Id
	 * @param $num 获取搭配数目
	 * @param $sort best, new, recommend
	 */
	public function getUserDapeiImage($uid, $num, $sort){
		$where = " WHERE uid= " . $uid;
		$limit = " limit 0, ".$num;
		switch($sort){
			//24小时最热 24小时喜欢人数
			case 'hot1':
				$sort = " ORDER BY collect_1count DESC,share_id DESC ";
				break;
			//1周最热 1周喜欢人数
			case 'hot7':
				$sort = " ORDER BY collect_7count DESC,share_id DESC ";
				break;
			//最新
			case 'new':
				$sort = " ORDER BY share_id DESC ";
				break;
			default:
				$sort = '';
				break;
		}
		$sql = 'SELECT DISTINCT(share_id) FROM '.FDB::table('share_dapei_index').$where.$sort.$limit;
		$res = FDB::query($sql);
		while($data = FDB::fetch($res)){
			$shareList[$data['share_id']] = false;
		}
		if(count($shareList) > 0){
			$shareIds = array_keys($shareList);
			$sql = 'SELECT share_id,uid,content,collect_count,comment_count,create_time,cache_data
					FROM '.FDB::table('share').' WHERE share_id IN ('.implode(',',$shareIds).')';
			$res = FDB::query($sql);
			while($data = FDB::fetch($res)){
				$img = fStripslashes(unserialize($data['cache_data']));
				$shareList[$data['share_id']] = current($img["imgs"]["all"]);
			}
			return $shareList;
		}
	}
}
?>