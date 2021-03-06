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
 * 选款师
 +------------------------------------------------------------------------------
 */
class UserDarenAction extends CommonAction
{
	public function index()
	{
		$where = '';
		$parameter = array();
		$user_name = trim($_REQUEST['user_name']);
		$day_time = trim($_REQUEST['day_time']);
		$cid = intval($_REQUEST['cid']);
		$status = !isset($_REQUEST['status']) ? -1 : intval($_REQUEST['status']);
		
		$is_empty = false;
		if(!empty($user_name))
		{
			$this->assign("user_name",$user_name);
			$parameter['user_name'] = $user_name;
			$uid = (int)D('User')->where("user_name = '".$user_name."'")->getField('uid');
			if($uid == 0)
				$is_empty = true;
			else
				$where.=" AND ud.uid = ".$uid;
		}
		
		if(!empty($day_time))
		{
			$this->assign("day_time",$day_time);
			$parameter['day_time'] = $day_time;
			$day_time = strZTime($day_time);
			$where .= " AND ud.day_time = '$day_time'";
		}

		if(!empty($day_time))
		{
			$this->assign("day_time",$day_time);
			$parameter['day_time'] = $day_time;
			$day_time = strZTime($day_time);
			$where .= " AND ud.day_time = '$day_time'";
		}
		
		$join_sql = '';
		if($cid > 0)
		{
			$parameter['cid'] = $cid;
			$join_sql = ' INNER JOIN '.C("DB_PREFIX").'user_daren_cate AS udc ON udc.id = ud.id AND udc.cid = '.$cid;
		}
		$this->assign("cid",$cid);
		
		if($status > -1)
		{
			$this->assign("status",$status);
			$parameter['status'] = $status;
			$where .= " AND ud.status = $status";
		}
		else
			$this->assign("status",-1);
		
		if(!$is_empty)
		{
			$model = M();
			
			if(!empty($where))
				$where = 'WHERE 1' . $where;
			
			$sql = 'SELECT COUNT(ud.id) AS tcount 
				FROM '.C("DB_PREFIX").'user_daren AS ud '.$join_sql.' 
				INNER JOIN '.C("DB_PREFIX").'user AS u ON u.uid = ud.uid '.$where;
			
			$count = $model->query($sql);
			$count = $count[0]['tcount'];
			
			$sql = 'SELECT ud.*,u.user_name  
				FROM '.C("DB_PREFIX").'user_daren AS ud '.$join_sql.' 
				INNER JOIN '.C("DB_PREFIX").'user AS u ON u.uid = ud.uid '.$where;
			
			$daren_cate = D("DarenCate")->where('status = 1')->select();
			$this->assign ('daren_cate',$daren_cate);
	
			$this->_sqlList($model,$sql,$count,$parameter);
		}
		$this->display();
	}

	public function add()
	{
		$daren_cate = D("DarenCate")->where('status = 1')->select();
		$this->assign ('daren_cate',$daren_cate);
		parent::add();
	}
	
	public function insert()
	{
		$_POST['is_best'] = intval($_REQUEST['is_best']);
		$_POST['is_index'] = intval($_REQUEST['is_index']);
		$uid = (int)$_REQUEST['uid'];
		$name=$this->getActionName();
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		
		//保存当前数据对象
		$id=$model->add ();
		if ($id!==false)
		{
			if($upload_list = $this->uploadImages())
			{
				foreach($upload_list as $upload_item)
				{
					$img = $upload_item['recpath'].$upload_item['savename'];
					if($upload_item['key'] == 'img_file')
						D("UserDaren")->where('id = '.$id)->setField('img',$img);
					elseif($upload_item['key'] == 'index_img_file')
						D("UserDaren")->where('id = '.$id)->setField('index_img',$img);
				}
			}
			
			$cids = array();
			foreach($_POST['cid'] as $cid)
			{
				$cids[] = $cid;
				D('UserDarenCate')->add(array('id'=>$id,
					'cid'=>$cid,
					'uid'=>$_POST['uid'],
					'sort'=>(int)$_POST['cid_sort'][$cid],
					'content'=>$_POST['cid_content'][$cid])
				);
			}
			D("UserDaren")->where('id = '.$id)->setField('cids',implode(',',$cids));
			D("User")->where('uid = '.$uid)->setField('is_daren',1);
			
			Vendor("common");
			clearCacheDir('daren');
			
			$this->saveLog(1,$id);
			$this->assign ( 'jumpUrl', Cookie::get ( '_currentUrl_' ) );
			$this->success (L('ADD_SUCCESS'));
		} else {
			//失败提示
			$this->saveLog(0,$id);
			$this->error (L('ADD_ERROR'));
		}
	}
	
	public function edit()
	{
		$id = intval($_REQUEST['id']);
		$vo = D("UserDaren")->getById($id);
		$user_name = D("User")->where('uid = '.$vo['uid'])->getField('user_name');
		$this->assign ('vo',$vo);
		$this->assign ('user_name',$user_name);

		$user_daren_cate = D("UserDarenCate")->where('id = '.$id)->select();
		$user_daren_cates = array();
		foreach($user_daren_cate as $cate)
		{
			$user_daren_cates[$cate['cid']]['sort'] = $cate['sort'];
			$user_daren_cates[$cate['cid']]['content'] = $cate['content'];
		}

		$daren_cate = D("DarenCate")->where('status = 1')->select();
		foreach($daren_cate as $key => $cate)
		{
			$daren_cate[$key]['check'] = false;
			$daren_cate[$key]['daren_sort'] = 100;
			$daren_cate[$key]['daren_content'] = '';
			if(isset($user_daren_cates[$cate['id']]))
			{
				$daren_cate[$key]['check'] = true;
				$daren_cate[$key]['daren_sort'] = $user_daren_cates[$cate['id']]['sort'];
				$daren_cate[$key]['daren_content'] = $user_daren_cates[$cate['id']]['content'];
			}
		}
		$this->assign ('daren_cate',$daren_cate);
		$this->display();
	}
	
	public function update()
	{
		$id = intval($_REQUEST['id']);
		$_POST['is_best'] = intval($_REQUEST['is_best']);
		$_POST['is_index'] = intval($_REQUEST['is_index']);
		$name=$this->getActionName();
		$model = D ( $name );
		if (false === $data = $model->create ()) {
			$this->error ( $model->getError () );
		}
		
		// 更新数据
		$list=$model->save($data);
		if (false !== $list)
		{
			if($upload_list = $this->uploadImages())
			{
				$daren = D("UserDaren")->getById($id);
				foreach($upload_list as $upload_item)
				{
					$img = $upload_item['recpath'].$upload_item['savename'];
					if($upload_item['key'] == 'img_file')
					{
						@unlink(FANWE_ROOT.$daren['img']);
						D("UserDaren")->where('id = '.$id)->setField('img',$img);
					}
					elseif($upload_item['key'] == 'index_img_file')
					{
						@unlink(FANWE_ROOT.$daren['index_img']);
						D("UserDaren")->where('id = '.$id)->setField('index_img',$img);
					}
				}
			}
			
			$cids = array();
			D('UserDarenCate')->where('id = '.$id)->delete();
			foreach($_POST['cid'] as $cid)
			{
				$cids[] = $cid;
				D('UserDarenCate')->add(array('id'=>$id,
					'cid'=>$cid,
					'uid'=>$_POST['uid'],
					'sort'=>(int)$_POST['cid_sort'][$cid],
					'content'=>$_POST['cid_content'][$cid])
				);
			}
			D("UserDaren")->where('id = '.$id)->setField('cids',implode(',',$cids));
			
			Vendor("common");
			clearCacheDir('daren');
			
			$this->saveLog(1,$id);
			$this->assign('jumpUrl', Cookie::get ( '_currentUrl_' ) );
			$this->success (L('EDIT_SUCCESS'));
		}
		else
		{
			//错误提示
			$this->saveLog(0,$id);
			$this->error (L('EDIT_ERROR'));
		}
	}
	
	public function toggleStatus()
	{
		$id = intval($_REQUEST['id']);
		if($id == 0)
			exit;
		
		$val = intval($_REQUEST['val']) == 0 ? 1 : 0;
			
		$field = trim($_REQUEST['field']);
		if(empty($field))
			exit;
		
		$result = array('isErr'=>0,'content'=>'');
		$name=$this->getActionName();
		$model = D($name);
		$pk = $model->getPk();
		if(false !== $model->where($pk.' = '.$id)->setField($field,$val))
		{
			if($field == 'status')
			{
				$uid = (int)$model->where($pk.' = '.$id)->getField('uid');
				D("User")->where('uid = '.$uid)->setField('is_daren',$val);
			}

			$this->saveLog(1,$id,$field);
			$result['content'] = $val;
			
			Vendor("common");
			clearCacheDir('daren');
		}
		else
		{
			$this->saveLog(0,$id,$field);
			$result['isErr'] = 1;
		}
		
		die(json_encode($result));
	}
	
	public function remove()
	{
		//删除指定记录
		$result = array('isErr'=>0,'content'=>'');
		$id = $_REQUEST['id'];
		if(!empty($id))
		{
			$model = D("UserDaren");
			$condition = array('id' => array('in',explode (',',$id)));
			$list = $model->where($condition)->select();
			
			if(false !== $model->where ($condition)->delete())
			{
				D('UserDarenCate')->where($condition)->delete();
				foreach($list as $item)
				{
					if(!empty($item['img']))
						@unlink(FANWE_ROOT.$item['img']);
						
					if(!empty($item['index_img']))
						@unlink(FANWE_ROOT.$daren['index_img']);
						
					D("User")->where('uid = '.$item['uid'])->setField('is_daren',0);
				}
				
				Vendor("common");
				clearCacheDir('daren');
			
				$this->saveLog(1,$id);
			}
			else
			{
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
}
?>