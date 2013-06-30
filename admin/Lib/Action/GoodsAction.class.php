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
 商品管理
 +------------------------------------------------------------------------------
 */
class GoodsAction extends CommonAction
{
	public function index()
	{
		vendor("common");
		$where = '';
		$parameter = array();
		$keyword = trim($_REQUEST['keyword']);
		$cate_id = intval($_REQUEST['cate_id']);
		$color = intval($_REQUEST['color']);
		$keyid = trim($_REQUEST['keyid']);
		
		if(!empty($keyid))
		{
			$this->assign("keyid",$keyid);
			$parameter['keyid'] = $keyid;
			$where = " AND g.keyid = '".$keyid."' ";
		}
		
		$is_match = false;
		if(!empty($keyword))
		{
			$this->assign("keyword",$keyword);
			$parameter['keyword'] = $keyword;
			$match_key = FS('Words')->segmentToUnicode($keyword,'+');
			$is_match = true;
		}
		
		if($color > 0)
		{
			$this->assign("color",$color);
			$parameter['color'] = $color;

			$where .= " AND g.color = $color ";
		}
		
		$is_cate = false;
		if($cate_id != 0)
		{
			$is_cate = true;
			$this->assign("cate_id",$cate_id);
			$parameter['cate_id'] = $cate_id;

			if($cate_id > 0)
			{
				$child_ids = D('GoodsCategory')->getChildIds($cate_id,'cate_id');
				$child_ids[] = $cate_id;
				$where .= " AND g.cid IN (".implode(',',$child_ids).") ";
			}
			else
				$where .= " AND g.cid = 0 ";
		}
		
		$model = M();
		
		$append_sql = '';
		$sql_count = 'SELECT COUNT(g.id) AS gcount FROM '.C("DB_PREFIX").'goods AS g ';
		$sql = 'SELECT g.*,gc.cate_name,gco.name AS color_name FROM '.C("DB_PREFIX").'goods AS g ';
		if($is_match)
		{
			$sql_count = 'SELECT COUNT(gm.id) AS gcount FROM '.C("DB_PREFIX").'goods_match AS gm ';
			$sql = 'SELECT g.*,gc.cate_name,gco.name AS color_name FROM '.C("DB_PREFIX").'goods_match AS gm ';
			$append_sql = 'INNER JOIN '.C("DB_PREFIX").'goods AS g ON g.id = gm.id ';
			$sql .= $append_sql;
			if($is_cate)
			{
				$sql_count .= $append_sql.$where;
				$sql .= $where;
			}
			$where = " AND match(gm.goods_name) against('".$match_key."' IN BOOLEAN MODE) ";
		}
		
		$sql .= ' LEFT JOIN '.C("DB_PREFIX").'goods_category AS gc ON gc.cate_id = g.cid 
			LEFT JOIN '.C("DB_PREFIX").'goods_color AS gco ON gco.id = g.color ';
		
		if(!empty($where))
			$where = str_replace('WHERE AND','WHERE','WHERE'.$where);
		
		$sql_count .= $where;
		$sql .= $where;
		
		$count = $model->query($sql_count);
		$count = $count[0]['gcount'];

		$this->_sqlList($model,$sql,$count,$parameter,'g.id');
		
		$root_id = D('GoodsCategory')->where('is_root = 1')->getField('cate_id');
		$root_id = intval($root_id);
		$root_ids = D('GoodsCategory')->getChildIds($root_id,'cate_id');
		$root_ids[] = $root_id;
		
		$cate_list = D('GoodsCategory')->where('cate_id not in ('.implode(',',$root_ids).')')->order('sort asc')->select();
		$cate_list = D('GoodsCategory')->toFormatTree($cate_list,'cate_name','cate_id','parent_id');
		$this->assign("cate_list",$cate_list);
		
		$color_list = D('GoodsColor')->order('sort asc')->select();
		$this->assign("color_list",$color_list);
		$this->display();
	}
	
	public function check()
	{
		$model = M();
		$sql_count = 'SELECT COUNT(id) AS gcount FROM '.C("DB_PREFIX").'goods_check ';
		$sql = 'SELECT g.*,gc.cate_name,gco.name AS color_name FROM '.C("DB_PREFIX").'goods_check AS gk 
			INNER JOIN '.C("DB_PREFIX").'goods AS g ON g.id = gk.id 
			LEFT JOIN '.C("DB_PREFIX").'goods_category AS gc ON gc.cate_id = g.cid 
			LEFT JOIN '.C("DB_PREFIX").'goods_color AS gco ON gco.id = g.color ';
		
		$count = $model->query($sql_count);
		$count = $count[0]['gcount'];

		$this->_sqlList($model,$sql,$count,$parameter,'g.id',true);
		$this->display();
	}
	
	public function checkOk()
	{
		$result = array('isErr'=>0,'content'=>'');
		$id = $_REQUEST['id'];
		if(!empty($id))
		{
			$model = D('GoodsCheck');
			$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
			if(false !== $model->where ( $condition )->delete ())
			{
				$this->saveLog(1,$id);
			}
			else
			{
				$this->saveLog(0,$id);
				$result['isErr'] = 1;
				$result['content'] = L('REMOVE_ERROR');
			}
		}
		else
		{
			$result['isErr'] = 1;
			$result['content'] = L('ACCESS_DENIED');
		}
		die(json_encode($result));
	}
	
	public function disables()
	{
		$where = '';
		$parameter = array();
		$keyid = trim($_REQUEST['keyid']);
		
		if(!empty($keyid))
		{
			$this->assign("keyid",$keyid);
			$parameter['keyid'] = $keyid;
			$where = " WHERE keyid = '".$keyid."' ";
		}
		
		$model = M();
		$sql_count = 'SELECT COUNT(id) AS gcount FROM '.C("DB_PREFIX").'goods_disable '.$where;
		$sql = 'SELECT * FROM '.C("DB_PREFIX").'goods_disable'.$where;
		
		$count = $model->query($sql_count);
		$count = $count[0]['gcount'];

		$this->_sqlList($model,$sql,$count,$parameter,'id');
		$this->display();
	}
	
	public function removeDisables()
	{
		$result = array('isErr'=>0,'content'=>'');
		$id = $_REQUEST['id'];
		if(!empty($id))
		{
			$model = D('GoodsDisable');
			$condition = array ('id' => array ('in', explode ( ',', $id ) ) );
			if(false !== $model->where ( $condition )->delete ())
			{
				$this->saveLog(1,$id);
			}
			else
			{
				$this->saveLog(0,$id);
				$result['isErr'] = 1;
				$result['content'] = L('REMOVE_ERROR');
			}
		}
		else
		{
			$result['isErr'] = 1;
			$result['content'] = L('ACCESS_DENIED');
		}
		die(json_encode($result));
	}
	
	public function edit()
	{
		$id = (int)$_REQUEST['id'];
		$vo = D("Goods")->getById($id);
		$this->assign('vo',$vo);
		
		if($vo['shop_id'] > 0)
		{
			$shop = D("Shop")->getById($vo['shop_id']);
			$this->assign('shop',$shop);
		}
		
		$root_id = D('GoodsCategory')->where('is_root = 1')->getField('cate_id');
		$root_id = intval($root_id);
		$root_ids = D('GoodsCategory')->getChildIds($root_id,'cate_id');
		$root_ids[] = $root_id;
		
		$cate_list = D('GoodsCategory')->where('status = 1 AND cate_id not in ('.implode(',',$root_ids).')')->order('sort asc')->select();
		$cate_list = D('GoodsCategory')->toFormatTree($cate_list,'cate_name','cate_id','parent_id');
		$this->assign('cate_list',$cate_list);
		
		$color_list = D('GoodsColor')->order('sort asc')->select();
		$this->assign("color_list",$color_list);
		
		$this->display();
	}
	
	public function update()
	{
		$id = intval($_REQUEST['id']);
		$model = D("Goods");
		if (false === $data = $model->create ()) {
			$this->error ($model->getError());
		}
		
		$goods_img= '';
		if($upload_list = $this->uploadImages())
			$goods_img = $upload_list[0]['recpath'].$upload_list[0]['savename'];

		$old_goods = D("Goods")->where('id = '.$id)->find();
		
		// 更新数据
		$list=$model->save($data);
		if (false !== $list)
		{
			if(!empty($goods_img))
			{
				vendor("common");
				$img = array();
				$img['id'] = $old_goods['img_id'];
				$img['type'] = 'share';
				$img['src'] = FANWE_ROOT.$goods_img;
				FS('Image')->updateImage($img,true);
			}
			
			$this->saveLog(1,$id);
			$is_update = false;
			$goods_update_setting = array();
			if($old_goods['name'] != $data['name'])
			{
				$is_update = true;
				$goods_update_setting['name'] = 1;
			}
			
			$update_sql = array();
			if((float)$old_goods['price'] != (float)$data['price'])
			{
				$is_update = true;
				$goods_update_setting['price'] = 1;
				$update_sql[] = 'price = '.(float)$data['price'];
			}

			if((int)$old_goods['cid'] != (int)$data['cid'])
			{
				$is_update = true;
				$goods_update_setting['cate'] = 1;
			}

			if((int)$old_goods['color'] != (int)$data['color'])
			{
				$is_update = true;
				$goods_update_setting['color'] = 1;
				$update_sql[] = 'color = '.(int)$data['color'];
			}

			if((int)$old_goods['shop_id'] != (int)$data['shop_id'])
			{
				$is_update = true;
				$goods_update_setting['shop'] = 1;
				$goods_update_setting['shop_id'] = (int)$old_goods['shop_id'];
				$update_sql[] = 'shop_id = '.(int)$data['shop_id'];
			}

			if($is_update)
			{
				if(count($update_sql) > 0)
				{
					M()->query('UPDATE '.C("DB_PREFIX").'share_goods SET '.implode(',',$update_sql).' WHERE goods_id = '.$id);
				}
				$_SESSION['goods_update_setting'] = $goods_update_setting;
				$_SESSION['goods_update_id'] = $id;
				$this->redirect('Goods/updateShare');
			}
		}
		else
		{
			//错误提示
			$this->saveLog(0,$id);
			$this->error (L('EDIT_ERROR'));
		}
	}
	
	public function disable()
	{
		$_SESSION['goods_remove_ids'] = '';
		$id = $_REQUEST['id'];
		if(!empty($id))
		{
			$model = D("Goods");
			$condition = array('id' => array('in',explode(',',$id)));
			$list = $model->where ($condition)->select();
			$time = gmtTime();
			$goods_ids = array();
			$img_ids = array();
			foreach($list as $item)
			{
				$goods_ids[] = $item['id'];
				if($item['img_id'] > 0)
					$img_ids[] = $item['img_id'];
			}
			$goods_ids_str = implode(',',$goods_ids);
			$sql = 'REPLACE INTO '.C("DB_PREFIX")."goods_disable(type,keyid,name,url,create_time) 
				SELECT type,keyid,name,url,'".$time."' AS create_time FROM ".C("DB_PREFIX")."goods 
				WHERE id IN (".$goods_ids_str.")";
			M()->query($sql);
			
			if(false !== $model->where ( $condition )->delete())
			{
				//$sql = 'DELETE FROM '.C("DB_PREFIX")."goods_order WHERE goods_id IN (".$goods_ids.")";
				//M()->query($sql);
				
				$sql = 'DELETE FROM '.C("DB_PREFIX")."goods_comment WHERE goods_id IN (".$goods_ids_str.")";
				M()->query($sql);
				
				D('GoodsMatch')->where($condition)->delete();
				D('GoodsCheck')->where($condition)->delete();
				vendor("common");
				FS('Image')->deleteImages($img_ids);
				$_SESSION['goods_remove_ids'] = $goods_ids;
				$this->saveLog(1,$id);
				$this->redirect('Goods/removeShare');
			}
			else
			{
				$this->error(L('REMOVE_ERROR'));
			}
		}
		else
		{
			$this->error(L('ACCESS_DENIED'));
		}
	}
	
	public function remove()
	{
		$_SESSION['goods_remove_ids'] = '';
		$id = $_REQUEST['id'];
		if(!empty($id))
		{
			$model = D("Goods");
			$condition = array('id' => array('in',explode(',',$id)));
			$list = $model->where ($condition)->select();
			if(false !== $model->where ($condition)->delete())
			{
				D('GoodsMatch')->where($condition)->delete();
				D('GoodsCheck')->where($condition)->delete();
				$time = gmtTime();
				$goods_ids = array();
				$img_ids = array();
				foreach($list as $item)
				{
					$goods_ids[] = $item['id'];
					if($item['img_id'] > 0)
						$img_ids[] = $item['img_id'];
				}
				$goods_ids_str = implode(',',$goods_ids);
				//$sql = 'DELETE FROM '.C("DB_PREFIX")."goods_order WHERE goods_id IN (".implode(',',$goods_ids).")";
				//M()->query($sql);
				
				$sql = 'DELETE FROM '.C("DB_PREFIX")."goods_comment WHERE goods_id IN (".$goods_ids_str.")";
				M()->query($sql);
				
				vendor("common");
				FS('Image')->deleteImages($img_ids);
				
				$_SESSION['goods_remove_ids'] = $goods_ids;
				$this->saveLog(1,$id);
				$this->redirect('Goods/removeShare');
			}
			else
			{
				$this->error(L('REMOVE_ERROR'));
			}
		}
		else
		{
			$this->error(L('ACCESS_DENIED'));
		}
	}
	
	public function removeShare()
	{
		$this->display('remove');
		$goods_ids = $_SESSION['goods_remove_ids'];
		$index = (int)$_REQUEST['index'];
		$count = 100;
		$min = $index * $count;
		$max = $min + $count;
		$sql = 'SELECT id,share_id FROM '.C("DB_PREFIX").'share_goods 
			WHERE goods_id IN ('.implode(',',$goods_ids).') GROUP BY share_id 
			ORDER BY share_id ASC LIMIT 0,'.$count;
		
		$list = M()->query($sql);
		if($list && count($list) > 0)
		{
			$ids = array();
			$share_ids = array();

			echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('DELETE_TIPS_1'),$min,$max).'\',1);</script>');
			vendor("common");
			@set_time_limit(0);
			if(function_exists('ini_set'))
				ini_set('max_execution_time',0);
			
			foreach($list as $item)
			{
				D("Share")->removeHandler($item['share_id']);
				$ids[] = (int)$item['id'];
				$share_ids[] = (int)$item['share_id'];
				usleep(10);
			}
			usleep(100);
			if(count($ids) > 0)
			{
				M()->query('DELETE FROM '.C("DB_PREFIX")."share_goods WHERE id IN (".implode(',',$ids).")");
				M()->query('DELETE FROM '.C("DB_PREFIX")."share_goods_index WHERE share_id IN (".implode(',',$share_ids).")");
			}
			$index++;
			echoFlush('<script type="text/javascript">showmessage(\''.U('Goods/removeShare',array('index'=>$index)).'\',2);</script>');
			exit;
		}
		usleep(500);

		$_SESSION['goods_remove_ids'] = '';
		echoFlush('<script type="text/javascript">showmessage(\''.L('DELETE_TIPS_2').'\',3);</script>');
	}

	public function updateShare()
	{
		$this->display('update');
		$goods_id = (int)$_SESSION['goods_update_id'];
		$index = (int)$_REQUEST['index'];
		$index = max(1,$index);
		
		$sql = 'SELECT share_id FROM '.C("DB_PREFIX").'share_goods 
		WHERE goods_id = '.$goods_id.' GROUP BY share_id 
		ORDER BY share_id ASC LIMIT '.($index - 1).',1';
		
		$list = M()->query($sql);
		vendor("common");
		
		if($list && count($list) > 0)
		{
			$share_id = $list[0]['share_id'];
			echoFlush('<script type="text/javascript">showmessage(\''.sprintf(L('UPDATE_TIPS_1'),$index).'\',1);</script>');
			@set_time_limit(0);
			if(function_exists('ini_set'))
				ini_set('max_execution_time',0);
			
			$share = FS("Share")->getShareById($share_id);
			if($share)
			{
				$goods_ids = array();
				$share['cache_data'] = fStripslashes(unserialize($share['cache_data']));
				foreach($share['cache_data']['imgs']['all'] as $img)
				{
					if($img['type'] == 'g')
					{
						$goods_ids[] = $img['goods_id'];
					}
				}

				$color_ids = array();
				$goods_prices = array();
				$shop_ids = array();
				$cate_ids = array();

				$sql = 'SELECT * FROM '.FDB::table('goods').' WHERE id IN ('.implode(',',$goods_ids).')';
				$gres = FDB::query($sql);
				while($gdata = FDB::fetch($gres))
				{
					$goods_prices[] = (float)$gdata['price'];
						
					if((int)$gdata['shop_id'] > 0)
						$shop_ids[] = (int)$gdata['shop_id'];

					if((int)$gdata['cid'] > 0)
						$cate_ids[] = (int)$gdata['cid'];

					if((int)$gdata['color'] > 0)
						$color_ids[] = (int)$gdata['color'];
				}

				$goods_list = FDB::fetchAll('SELECT * FROM '.FDB::table('goods').' WHERE id IN ('.implode(',',$goods_ids).')');

				if(isset($_SESSION['goods_update_setting']['name']))
				{
					FS("Share")->updateShareMatch($share_id);
				}

				if(isset($_SESSION['goods_update_setting']['price']))
				{
					sort($goods_prices);
					$share_index = array();
					$share_index['min_price'] = current($goods_prices);
					$share_index['max_price'] = end($goods_prices);
					FDB::update("share_goods_index",$share_index,'share_id = '.$share_id);
				}

				if(isset($_SESSION['goods_update_setting']['cate']))
				{
					FS("Share")->updateShareCate($share_id,$cate_ids);
				}

				if(isset($_SESSION['goods_update_setting']['color']))
				{
					FS("Share")->updateShareColor($share_id,$color_ids);
				}

				if(isset($_SESSION['goods_update_setting']['shop']))
				{
					if(count($shop_ids) > 0)
					{
						FS("Shop")->saveShopShare($shop_ids,$share_id,$share['uid']);
						FS("Shop")->updateShopStatistic($shop_ids);
					}
				}
			}
			
			$index++;
			echoFlush('<script type="text/javascript">showmessage(\''.U('Goods/updateShare',array('index'=>$index)).'\',2);</script>');
			exit;
		}
		usleep(500);
		
		if(isset($_SESSION['goods_update_setting']['shop_id']) && (int)$_SESSION['goods_update_setting']['shop_id'] > 0)
			FS("Shop")->updateShopStatistic(array($_SESSION['goods_update_setting']['shop_id']));

		$_SESSION['goods_update_id'] = 0;
		$_SESSION['goods_update_setting'] = array();
		echoFlush('<script type="text/javascript">showmessage(\''.L('UPDATE_TIPS_2').'\',3);</script>');
	}
}

function getGoodsNameLink($name,$link)
{
	return '<a href="'.$link.'" target="_blank">'.$name.'</a>';
}
?>