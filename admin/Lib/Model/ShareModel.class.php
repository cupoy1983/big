<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: awfigq <awfigq@qq.com>
// +----------------------------------------------------------------------
/**
 +------------------------------------------------------------------------------
 * 管理员模型
 +------------------------------------------------------------------------------
 */
class ShareModel extends CommonModel
{
	public function removeHandler($share_ids)
	{
		if(!is_array($share_ids))
			$share_ids = array($share_ids);
			
		$condition = array ('share_id' => array ('in',$share_ids));
        $res = D('Share')->where ( $condition )->select();
		
		foreach($res as $item)
		{
			$share_id = intval($item['share_id']);
			switch($item['type'])
			{
				case 'bar':
					FS("Topic")->deleteTopic($item['rec_id']);
				break;
				case 'album':
					FS('Album')->deleteAlbum($item['rec_id']);
				break;
				case 'album_item':
					FS('Album')->deleteAlbumItem($share_id);
				break;
				case 'activity':
					FS('Activity')->delete($item['rec_id']);
				break;	
				case 'activity_post':
					FS('Activity')->deletePost($share_id);
				break;
				case 'vote':
					FS('Vote')->delete($item['rec_id']);
				break;	
				case 'vote_post':
					FS('Vote')->deletePost($share_id);
				break;	
				default:
					FS("Share")->deleteShare($share_id);
				break;
			}
		}
	}
}
?>