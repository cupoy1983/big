<?php
class sharelistMapi
{
	public function run()
	{
		global $_FANWE;	
		$root = array();
		$root['return'] = 1;

		$is_hot = (int)$_FANWE['requestData']['is_hot'];
		$is_new = (int)$_FANWE['requestData']['is_new'];
		$cid = (int)$_FANWE['requestData']['cid'];
		$tag = trim($_FANWE['requestData']['tag']);
		$keywords = trim($_FANWE['requestData']['keywords']);
		$is_spare_flow = (int)$_FANWE['requestData']['is_spare_flow'];
		$page = (int)$_FANWE['requestData']['page'];
		$page = max(1,$page);

		$img_width = 200;
		//$max_height = 400;
		$scale = 2;
		if($is_spare_flow == 1)
		{
			$scale = 1;
		}

		$condition = '';
		$is_tag = false;
		$is_cate = false;
		$sort = '';
		
		$tag = urldecode($tag);
		if(!empty($tag))
		{
			$is_tag = true;
			$condition.=" AND st.tag_name = '$tag'";
		}
		
		$is_empty = false;
		$is_match = false;
		$keywords = urldecode($keywords);
		if(!empty($keywords))
		{
			$match_key = FS('Words')->segmentToUnicode($keywords,'+');
			if(empty($match_key))
				$is_empty = true;
			else
			{
				$is_tag = false;
				$is_match = true;
				$condition = " match(sm.content_match) against('".$match_key."' IN BOOLEAN MODE)";
			}
		}

		if($cid > 0 && isset($_FANWE['cache']['goods_category']['all'][$cid]))
		{
			if($_FANWE['cache']['goods_category']['all'][$cid]['is_root'] == 0)
				$is_cate = true;
			else
				$cid = 0;
		}
		else
			$cid = 0;
			
		if($is_hot == 1)
		{
			$is_new = 0;
			$sort = " ORDER BY sgi.collect_1count DESC,sgi.share_id DESC";
		}
		elseif($is_new == 1)
		{
			$sort = " ORDER BY sgi.share_id DESC";
			$is_hot = 0;
		}

		$sql = 'SELECT DISTINCT(sgi.share_id) FROM '.FDB::table('share_goods_index').' AS sgi ';
		$sql_count = 'SELECT COUNT(DISTINCT sgi.share_id) FROM '.FDB::table('share_goods_index').' AS sgi ';
		$sql_type = '';
		if($is_tag)
		{
			$sql = 'SELECT DISTINCT(sgi.share_id) FROM '.FDB::table('share_tags').' AS st ';
			$sql_count = 'SELECT COUNT(DISTINCT sgi.share_id) FROM '.FDB::table('share_tags').' AS st ';
			$sql_type = 'st';
		}

		if($is_match)
		{
			$sql = 'SELECT DISTINCT(sgi.share_id) FROM '.FDB::table('share_goods_match').' AS sm ';
			$sql_count = 'SELECT COUNT(DISTINCT sm.share_id) FROM '.FDB::table('share_goods_match').' AS sm ';
			$sql_type = 'sm';
		}
		
		if($is_cate)
		{
			$sql_type = 'sc';
			if($is_tag)
			{
				$append_sql = 'INNER JOIN '.FDB::table('share_category').' AS sc 
					ON sc.share_id = st.share_id AND sc.cate_id = '.$cid.' ';
				$sql .= $append_sql;
				$sql_count .= $append_sql;
			}
			elseif($is_match)
			{
				$append_sql = 'INNER JOIN '.FDB::table('share_category').' AS sc 
					ON sc.share_id = sm.share_id AND sc.cate_id = '.$cid.' ';
				$sql .= $append_sql;
				$sql_count .= $append_sql;
			}
			else
			{
				$sql = 'SELECT DISTINCT(sgi.share_id) FROM '.FDB::table('share_category').' AS sc ';
				$sql_count = 'SELECT COUNT(DISTINCT sgi.share_id) FROM '.FDB::table('share_category').' AS sc ';
				$condition .= " AND sc.cate_id = ".$cid.' ';
			}
		}

		if($sql_type != '')
		{
			$append_sql = 'INNER JOIN '.FDB::table('share_goods_index').' AS sgi 
				ON sgi.share_id = '.$sql_type.'.share_id ';
		}
		if(!empty($condition))
			$condition = str_replace('WHERE AND','WHERE ','WHERE'.$condition);

		$sql .= $append_sql.$condition.$sort;
		$sql_count .= $append_sql.$condition;

		if(!$is_empty)
		{
			$args = md5($is_hot.'/'.$is_new.'/'.$cid.'/t'.$tag.'/k'.$keywords.'/'.$is_spare_flow.'/'.$page);
			$key = 'm/sharelist/'.substr($args,0,2).'/'.substr($args,2,2).'/'.$args;
			$cache_list = getCache($key);
			
			if($cache_list === NULL || (TIME_UTC - $cache_list['cache_time']) > 600)
			{
				$total = FDB::resultFirst($sql_count);
				
				$page_size = 20;//PAGE_SIZE;
				$page_total = max(ceil($total/$page_size),1);
				if($page > $page_total)
					$page = $page_total;

				$limit = (($page - 1) * $page_size).",".$page_size;
				$share_list = array();
				$res = FDB::query($sql.' LIMIT '.$limit);
				while($data = FDB::fetch($res))
				{
					$share_list[$data['share_id']] = false;
				}
				
				if(count($share_list) > 0)
				{
					$share_ids = array_keys($share_list);
					$format_images = array();
					$sql = 'SELECT share_id,cache_data 
						FROM '.FDB::table('share').' WHERE share_id IN ('.implode(',',$share_ids).')';
					$res = FDB::query($sql);
					while($item = FDB::fetch($res))
					{
						$cache_data = fStripslashes(unserialize($item['cache_data']));
						$img = $cache_data['imgs']['all'][$cache_data['imgs']['goods'][0]];
						$data = array();
						$data['share_id'] = $item['share_id'];
						$share_list[$item['share_id']] = $data;
						$format_images[$img['img_id']][] = &$share_list[$item['share_id']];
					}
					$share_list = array_slice($share_list,0,count($share_list));
				}
				
				if(count($format_images) > 0)
				{
					$img_ids = array_keys($format_images);
					$image_list = FS('Image')->getImageListByIds($img_ids);
					
					foreach($image_list as $img_id => $img)
					{
						foreach($format_images[$img_id] as $ikey => $val)
						{
							$format_images[$img_id][$ikey]['img'] = getImgName($img['src'],200,999,0,true);
							$height = $img['height'] * (200 / $img['width']);
							$format_images[$img_id][$ikey]['height'] = round($height / $scale);
						}
					}
				}
				
				$cache_list = array();
				$cache_list['page_total'] = $page_total;
				$cache_list['share_list'] = $share_list;
				$cache_list['cache_time'] = TIME_UTC;
				setCache($key,$cache_list);
			}
			else
			{
				$page_total = $cache_list['page_total'];
				$share_list = $cache_list['share_list'];
			}
		}
		else
		{
			$share_list = array();
			$page_total = 0;
		}

		$root['tag'] = $tag;
		$root['keywords'] = $keywords;
		$root['cid'] = $cid;
		$root['item'] = $share_list;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);

		if($page == 1)
		{
			FanweService::instance()->cache->loadCache('madv');
			$advs = $_FANWE['cache']['madv']['sharelist'];
			if($advs)
			{
				foreach($advs as $adv)
				{
					$adv['img'] = FS("Image")->getImageUrl($adv['img'],2);
					if($adv['type'] == 1)
					{
						$tag_count = count($adv['data']['tags']);
						unset($adv['data']);
						$adv['data']['count'] = $tag_count;
					}
					elseif($adv['type'] != 2 && $adv['type'] != 8)
						unset($adv['data']);
					unset($adv['sort'],$adv['status'],$adv['page']);
					$root['advs'][] = $adv;
				}
			}
		}
		m_display($root);
	}
}
?>