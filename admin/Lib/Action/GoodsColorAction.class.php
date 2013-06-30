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

 +------------------------------------------------------------------------------
 */
class GoodsColorAction extends CommonAction
{
	public function insert()
	{
		$model = D("GoodsColor");
		if(false === $data = $model->create())
		{
			$this->error($model->getError());
		}
		
		//保存当前数据对象
		$list=$model->add($data);
		if ($list !== false)
		{
			if($upload_list = $this->uploadImages())
			{
				$icon = $upload_list[0]['recpath'].$upload_list[0]['savename'];
				D("GoodsColor")->where('id = '.$list)->setField('icon',$icon);
			}
			
			$this->saveLog(1,$list);
			$this->success (L('ADD_SUCCESS'));

		}
		else
		{
			$this->saveLog(0,$list);
			$this->error (L('ADD_ERROR'));
		}
	}
	
	public function update()
	{
		$id = intval($_REQUEST['id']);
		$model = D("GoodsColor");
		if (false === $data = $model->create ()) {
			$this->error ( $model->getError () );
		}
		
		// 更新数据
		$list=$model->save($data);
		if (false !== $list)
		{
			if($upload_list = $this->uploadImages())
			{
				$icon = $upload_list[0]['recpath'].$upload_list[0]['savename'];
				if(!empty($icon))
				{
					$old_icon = D("GoodsColor")->where('id = '.$id)->getField('icon');
					if(!empty($old_icon))
						@unlink(FANWE_ROOT.$old_icon);
						
					D("GoodsColor")->where('id = '.$id)->setField('icon',$icon);
				}
			}
				
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
			
			D("GoodsColor")->where($condition)->delete();
			D("ShareColor")->where(array("color_id" => array('in',explode(',',$id))))->delete();
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