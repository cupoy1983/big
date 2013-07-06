<?php
class NoteModule
{
	public function index()
	{
		NoteModule::show();
	}

	public function goods()
	{
		NoteModule::show('bao');
	}

	public function photo()
	{
		NoteModule::show('photo');
	}

	private function show($current_type = '')
	{
		global $_FANWE;
		$share_id = intval($_FANWE['request']['sid']);
		$id = intval($_FANWE['request']['id']);
		$share_detail = FS('Share')->getShareDetail($share_id);
		if($share_detail === false)
			fHeader("location: ".FU('index'));
		
		$share_detail['share_content'] = str_replace(array('#','@'),array('＃','＠'),preg_replace("/[\r\n]/",'',$share_detail['content']));
		$_FANWE['PAGE_SEO_SELF'] = array(
			'title'=>$share_detail['title'],
			'content'=>cutStr($share_detail['content'],80,''),
			'title'=>$share_detail['title'],
			'best_desc'=>$share_detail['best_desc'],
			'seo_title'=>$share_detail['seo_title'],
			'seo_keywords'=>$share_detail['seo_keywords'],
			'seo_description'=>$share_detail['seo_description'],
		);
		
		$cate_names = FS('Share')->getCategorysByShareId($share_id,true);
		$_FANWE['PAGE_SEO_SELF']['cate_name'] = implode(',',$cate_names);
		
		include fimport('dynamic/u');
		//FDB::query('UPDATE '.FDB::table('share').' SET click_count = click_count + 1 WHERE share_id = '.$share_id);

		//上一个，下一个分享
		$pns = FS('Share')->getPrevNextShares($share_detail['uid'],$share_id);

		//发布分享的会员
		$share_user = FS('User')->getUserById($share_detail['uid']);
		$_FANWE['PAGE_SEO_SELF']['user_name'] = $share_user['user_name'];
		
		//喜欢分享的会员
		$share_detail['collects'] = FS('Share')->getShareCollectUser($share_id);
		if(!isset($share_detail['collects'][$_FANWE['uid']]))
		{
			if(FS('Share')->getIsCollectByUid($share_id,$_FANWE['uid']))
				$share_detail['collects'][$_FANWE['uid']] = $_FANWE['uid'];
		}

		//会员显示名称
		$user_show_name = FS('User')->getUserShowName($share_detail['uid']);
		
		//会员勋章
		$user_medals = FS('User')->getUserMedal($share_detail['uid']);
		
		$_FANWE['PAGE_SEO_SELF']['tags'] = '';
		//分享标签
		$share_tags = $share_detail['cache_data']['tags']['user'];
		FS('Share')->tagsFormat($share_tags);
		$tag_first = true;
		
		$tag_names = array();
        foreach($share_tags as $seo_tag)
        {
            $_FANWE['PAGE_SEO_SELF']['tags'] .= ($tag_first ? '' : ',').$seo_tag['tag_name'];
			$tag_first = false;
			$tag_names[] = $seo_tag['tag_name'];
        }
		
		if($share_detail['type'] == 'album_item')
			$_FANWE['PAGE_SEO_SELF']['album_name'] = $share_detail['title'];

		//是否可编辑标签
		$is_eidt_tag = FS('Share')->getIsEditTag($share_detail);

		//喜欢分享的会员还喜欢
		//$fav_user_fav_share = FS('Share')->getCollectShareByShare($share_id);

		//发布分享的会员喜欢的分享
		$user_collect_share = FS('Share')->getCollectShareByUser($share_user['uid']);
		
		$related_shares = FS("Share")->getRelatedSharesByShareIdAndTags($share_id,$tag_names);
		
		//是否可删除标签
		$is_remove_comment = FS('Share')->getIsRemoveComment($share_detail);

		//分享评论
		$share_detail['comments'] = FS('Share')->getShareCommentList($share_id,'0,10');

		//分享评论分页
		$pager = buildPage('',array(),$share_detail['comment_count'],$_FANWE['page'],10);
		unset($share_detail['cache_data']);
			
		$current_obj = NULL;
		if($current_type == '' || $id == 0)
		{
			if(!empty($share_detail['imgs']))
			{
				$current_obj = current($share_detail['imgs']);
				if($current_obj['type'] == 'g')
				{
					$current_type = 'bao';
					$_FANWE['PAGE_SEO_SELF']['goods_title'] = $current_obj['name'];
					$_FANWE['PAGE_SEO_SELF']['goods_alt'] = $current_obj['alt'];
				}
				else
				{
					$current_type = 'photo';
					$_FANWE['PAGE_SEO_SELF']['photo_alt'] = $current_obj['alt'];
				}
			}
		}
		else
		{
			switch($current_type)
			{
				case 'bao':
					foreach($share_detail['imgs'] as $img)
					{
						$current_obj = $img;
						if($img['type'] == 'g' && $img['id'] == $id)
						{
							$_FANWE['PAGE_SEO_SELF']['goods_title'] = $current_obj['name'];
							$_FANWE['PAGE_SEO_SELF']['goods_alt'] = $current_obj['alt'];
							break;
						}
					}
				break;

				case 'photo':
					foreach($share_detail['imgs'] as $img)
					{
						$current_obj = $img;
						if($img['type'] == 'm' && $img['id'] == $id)
						{
							$_FANWE['PAGE_SEO_SELF']['photo_alt'] = $current_obj['alt'];
							break;
						}
					}
				break;
			}
		}

		switch($current_type)
		{
			case 'bao':
				//会员最被喜欢的宝贝
				$best_goods_share = FS('Share')->getBestCollectGoodsShareByUser($share_user['uid']);
				//会员分享店铺信息
				$shop_percent_html = FS('Shop')->getUserShareShopHtml($share_user['uid']);
			break;

			case 'photo':
				//会员最被喜欢的照片
				$best_photo_share = FS('Share')->getBestCollectPhotoShareByUser($share_user['uid']);

				//会员喜欢的照片
				$user_fav_photo = FS('Share')->getUserFavPhotoShare($share_user['uid']);
			break;

			default:
				//获取原文分享
				$share_detail['is_relay'] = false;
				$share_detail['is_rec'] = false;
				if($share_detail['parent_id'] > 0 && $share_detail['base_id'] > 0)
				{
					$share_detail['is_relay'] = true;
					$parent_share = FS('Share')->getShareDetail($share_detail['base_id']);
				}
				elseif($share_detail['rec_id'] > 0 && $share_detail['base_id'] > 0)
				{
					$share_detail['is_rec'] = true;
					$parent_share = FS('Share')->getShareDetail($share_detail['base_id']);
				}
				$current_type = 'other';
			break;
		}
		//获取goods_id和share_id供评论采集使用
		$goodShare = $current_obj['goods_id'].'||'.$current_obj['share_id'];
		include template('page/note/note_index');
		display();
	}
}
?>