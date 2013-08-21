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
 积分兑换商品
 +------------------------------------------------------------------------------
 */
class ExchangeGoodsAction extends CommonAction
{
	public function index()
	{
		$where = '';
		$parameter = array();
		$name = trim($_REQUEST['name']);
		$begin_time_str = trim($_REQUEST['begin_time']);
		$end_time_str = trim($_REQUEST['end_time']);
		
		$begin_time = !empty($begin_time_str) ? strZTime($begin_time_str) : 0;
		$end_time = !empty($end_time_str) ? strZTime($end_time_str) : 0;

		if(!empty($name))
		{
			$this->assign("name",$name);
			$parameter['name'] = $name;
            $like_name = mysqlLikeQuote($name);
            $where .= ' AND name LIKE \'%'.$like_name.'%\'';
		}
		
		if ($begin_time > 0)
		{
			$this->assign("begin_time",$begin_time_str);
			$parameter['begin_time'] = $begin_time_str;
			$where .= " AND begin_time >= '".$begin_time."'";
		}
		
		if ($end_time > 0)
		{
			$this->assign("end_time",$end_time_str);
			$parameter['end_time'] = $end_time_str;
			$where .= " AND end_time < '".($end_time + 86400)."'";
		}

		$model = M();

		if(!empty($where))
			$where = 'WHERE 1' . $where;

		$sql = 'SELECT COUNT(id) AS tcount
			FROM '.C("DB_PREFIX").'exchange_goods '.$where;

		$count = $model->query($sql);
		$count = $count[0]['tcount'];

		$sql = 'SELECT * FROM '.C("DB_PREFIX").'exchange_goods '.$where;
		$this->_sqlList($model,$sql,$count,$parameter,'id');
		$this->display();
		return;
	}
	
	public function insert()
	{
		$_POST['begin_time'] = strZTime($_POST['begin_time']);
		$_POST['end_time'] = strZTime($_POST['end_time']);
		$_POST['integral'] = (int)$_POST['integral'];
		$_POST['stock'] = (int)$_POST['stock'];
		$_POST['user_num'] = (int)$_POST['user_num'];
		$_POST['price'] = (float)$_POST['price'];
		$name=$this->getActionName();
		$model = D ($name);
		if(false === $data = $model->create())
		{
			$this->error($model->getError());
		}
		
		//保存当前数据对象
		$list=$model->add($data);
		if ($list !== false)
		{
			vendor("common");
			$image = array();
			$image['type'] = 'share';
			
			if($upload_list = $this->uploadImages())
			{
				$goods_img = $upload_list[0]['recpath'].$upload_list[0]['savename'];
				if(!empty($goods_img))
				{
					$image['src'] = FANWE_ROOT.$goods_img;
					$image = FS('Image')->addImage($image);
					$model->where("id=".$list)->setField("img_id",$image['id']);
				}
			}
			
			if($_REQUEST['apply_type'] == 0 || $_REQUEST['apply_type'] == 2)
			{
				$_FANWE['uid'] = $_REQUEST['uid'];
				$share = array();
				$share['share']['uid'] = $_FANWE['uid'];
				$share['share']['type'] = 'trial';
				$share['share']['rec_id'] = $list;
				$share['share']['title'] = htmlspecialchars($_REQUEST['name']);
				$share['share']['content'] = htmlspecialchars($_REQUEST['apply_content']);
				
				$share['rel_photo'][] = array(
					'type' => 'default',
					'img_id' => $image['id'],
					'sort' => 1,
					'base_id' => 0,
					'base_share' => 0,
				);
				$share = FS('Share')->save($share,false);
				if($share['status'])
				{
					FDB::query("UPDATE ".FDB::table("user_count")." SET trial = trial + 1 WHERE uid = ".$_FANWE['uid']);
					FDB::query("UPDATE ".FDB::table("exchange_goods")." SET share_id = ".$share['share_id']." WHERE id = ".$list);
					$this->saveLog(1,$list);
					$this->success (L('ADD_SUCCESS'));
				}
				else
				{
					$model->where("id=".$list)->delete();
					FS('Image')->deleteImages(array($image['img_id']));
					$this->saveLog(0,$list);
					$this->error (L('ADD_ERROR'));
				}
			}
		}
		else
		{
			$this->saveLog(0,$list);
			$this->error (L('ADD_ERROR'));
		}
	}
	
	public function edit()
	{
		$id = intval($_REQUEST['id']);
		$vo = D("ExchangeGoods")->getById($id);
		$user_name = D("User")->where('uid = '.$vo['uid'])->getField('user_name');
		$this->assign ('vo',$vo);
		$this->assign ('user_name',$user_name);
		$this->display();
	}
	
	public function update()
	{
		$_POST['begin_time'] = strZTime($_POST['begin_time']);
		$_POST['end_time'] = strZTime($_POST['end_time']);
		$_POST['integral'] = (int)$_POST['integral'];
		$_POST['stock'] = (int)$_POST['stock'];
		$_POST['user_num'] = (int)$_POST['user_num'];
		$_POST['price'] = (float)$_POST['price'];
		$id = intval($_REQUEST['id']);
		$name=$this->getActionName();
		$model = D ($name);
		if (false === $data = $model->create ()) {
			$this->error ( $model->getError () );
		}
		
		// 更新数据
		$list=$model->save($data);
		if (false !== $list)
		{
			vendor("common");
			$old_goods = $model->where('id = '.$id)->find();
			if($upload_list = $this->uploadImages())
			{
				$goods_img = $upload_list[0]['recpath'].$upload_list[0]['savename'];
				if(!empty($goods_img))
				{
					$img = array();
					$img['type'] = 'share';
					$img['src'] = FANWE_ROOT.$goods_img;
					if($old_goods['img_id'] > 0)
					{
						$img['id'] = $old_goods['img_id'];
						FS('Image')->updateImage($img,true);
					}
					else
					{
						$image = FS('Image')->addImage($image);
						$model->where('id = '.$id)->setField("img_id",$image['id']);
					}
				}
			}
			if($old_goods['share_id'] > 0)
				FS("Share")->updateShare($old_goods['share_id'],htmlspecialchars($_REQUEST['name']),htmlspecialchars($_REQUEST['apply_content']));
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
	
	public function remove()
	{
		//删除指定记录
		$result = array('isErr'=>0,'content'=>'');
		$id = $_REQUEST['id'];
		if(!empty($id))
		{
			$name=$this->getActionName();
			$model = D($name);
			$pk = $model->getPk ();
			
			$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
			$datas = $model->where($condition )->select();
			
			vendor("common");
			setTimeLimit();
			$img_ids = array();
			foreach($datas as $data)
			{
				$img_ids[] = $data['img_id'];
				FS('Exchange')->delete($data['id']);
			}
			FS('Image')->deleteImages($img_ids);
			$this->saveLog(0,$id);
		}
		else
		{
			$result['isErr'] = 1;
			$result['content'] = L('ACCESS_DENIED');
		}
		
		die(json_encode($result));
	}
	
	public function toggleStatus(){
		vendor("common");
		$id = intval($_REQUEST['id']);
		$val = intval($_REQUEST['val']) == 0 ? 1 : 0;
		if(empty($id)){
			exit;
		}
		if($val==1){
			//零库存的商品不允许恢复正常状态
			$goods = FS("Exchange")->getById($id);
			if($goods["buy_count"]>=$goods["stock"]){
				$result = array('isErr'=>1,'content'=>'库存不足，不允许上架');
				die(json_encode($result));
			}
		}
		
		parent::toggleStatus();
	}
}

function getTypeName($type)
{
	return L('GOODS_TYPE_'.($type));
}

function getApplyTypeName($type)
{
	return L('APPLY_TYPE_'.($type));
}
?>