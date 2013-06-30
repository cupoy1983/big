<?php
/*
 * 邮件订阅类
 */
class MailRssCategoryAction extends CommonAction
{
    public function index()
	{
		if(isset($_REQUEST['parent_id']))
			$parent_id = intval($_REQUEST['parent_id']);
		else
			$parent_id = intval($_SESSION['rss_category_parent_id']);
		
		$_SESSION['rss_category_parent_id'] = $parent_id;
		
		$map['parent_id'] = $parent_id;
		$model = D("MailRssCategory");
		if (! empty ( $model ))
		{
			$this->assign("parent_id",$parent_id);
			if($parent_id > 0)
			{
				$pp_id = $model->where('cate_id = '.$parent_id)->getField('parent_id');
				$this->assign("pp_id",$pp_id);
			}
			
			$count = $model->where('parent_id = '.$parent_id)->count('cate_id');
			$sql = 'SELECT gc.*,COUNT(gc1.cate_id) AS child 
					FROM '.C("DB_PREFIX").'mail_rss_category as gc 
					LEFT JOIN '.C("DB_PREFIX").'mail_rss_category as gc1 ON gc1.parent_id = gc.cate_id 
					WHERE gc.parent_id = '.$parent_id.' GROUP BY gc.cate_id';
			
			$this->_sqlList($model,$sql,$count,$map);
		}
		$this->display ();
		return;
	}
    public function insert()
	{
		$_POST['is_best'] = isset($_POST['is_best']) ? intval($_POST['is_best']) : 0;
		
		$model = D("MailRssCategory");
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
                            foreach($upload_list as $k=>$v){
                                $imgs[$v['key']] = $v['recpath'].$v['savename'];
                            }
                            D("MailRssCategory")->where('cate_id = '.$list)->save($imgs);
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
        public function add()
	{
		$cate_list = D("MailRssCategory")->where('status = 1')->field('cate_id,parent_id,cate_name')->order('sort ASC,cate_id ASC')->select();
		$cate_list = D("MailRssCategory")->toFormatTree($cate_list,'cate_name','cate_id','parent_id');
		$this->assign("cate_list",$cate_list);
		$this->display();
	}
        
        public function edit()
	{
		$id = intval($_REQUEST['cate_id']);
		$vo = D("MailRssCategory")->getById($id);
		$this->assign ( 'vo', $vo );
		
		$cate_list = D("MailRssCategory")->where('status = 1')->field('cate_id,parent_id,cate_name')->order('sort ASC,cate_id ASC')->select();
		$cate_list = D("MailRssCategory")->toFormatTree($cate_list,'cate_name','cate_id','parent_id');
		$this->assign("cate_list",$cate_list);

		$this->display();
	}
	
	public function update()
	{
		$id = intval($_REQUEST['cate_id']);
		
		$_POST['is_best'] = isset($_POST['is_best']) ? intval($_POST['is_best']) : 0;
		
		$model = D("MailRssCategory");
		if (false === $data = $model->create ()) {
			$this->error ( $model->getError () );
		}
		
		// 更新数据
		$list=$model->save($data);
		if (false !== $list)
		{
			if($upload_list = $this->uploadImages())
			{
                            $old_img = D("MailRssCategory")->where('cate_id = '.$id)->find();
                            foreach($upload_list as $k=>$v){
                                $imgs[$v['key']] = $v['recpath'].$v['savename'];
                                if(!empty($imgs[$v['key']])){
                                    if(!empty($old_img[$v['key']]))
					@unlink(FANWE_ROOT.$old_img[$v['key']]);
                                }
                            }
                            D("MailRssCategory")->where('cate_id = '.$id)->save($imgs);
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
			$model = D("MailRssCategory");
			$condition = array('cate_id' => array('in',explode (',',$id)));
			$condition1 = array('parent_id' => array('in',explode (',',$id)));
			if($model->where($condition1)->count() > 0)
			{
				$result['isErr'] = 1;
				$result['content'] = L('CATE_EXIST_CHILD');
			}
			else
			{
				if(false !== $model->where ( $condition )->delete())
				{
					$this->saveLog(1,$id);
				}
				else
				{
					$result['isErr'] = 1;
					$result['content'] = L('REMOVE_ERROR');
				}
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
         /************************************************
         *                  页面函数调用                *
         ***********************************************/
function getHandlerLinks($id,$cate)
{
	$links = '';

	if($cate['child'] > 0)
		$links = ' <a href="'.U('MailRssCategory/index',array('parent_id'=>$id)).'">'.L('SHOW_CHILD').'</a>';
	
	return trim($links);
}
?>
