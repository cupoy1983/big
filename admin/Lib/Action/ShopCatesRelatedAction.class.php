<?php
class ShopCatesRelatedAction extends CommonAction
{
    function index()
	{
        $cid = $_REQUEST['cid'];
        $cname = $_REQUEST['cname'];
        
        $model = M();
        $types = D("ShopCates")->field('type')->group('type')->select();
        
        $count = D('ShopCatesRelated')->where('cid = '.$cid)->count();
        $sql = 'SELECT scr.*,sc.name as cate_name FROM '.C("DB_PREFIX").'shop_cates_related AS scr  
			INNER JOIN '.C('DB_PREFIX').'shop_cates sc ON sc.id = scr.sc_id AND sc.type = scr.type 
			WHERE scr.cid='.$cid ;
   
        $this->_sqlList($model,$sql,$count,true);
        
        $this->assign('types',$types);
        $this->assign('cid',$cid);
        $this->assign('cname',$cname);
        $this->display ();
        return;
    }
	
    function setting()
	{
        $cid = $_REQUEST['cid'];
        $cate_info =  D('ShopCategory')->where("id=".$cid)->find();
        $type = $_REQUEST['type'];
        $cate_list = D('ShopCates')->where("type='$type' and pid = ''")->field('id,type,name')->order('sort asc')->select();
        $this->assign('cate_info',$cate_info);
        $this->assign('type',$type);
        $this->assign('cate_list',$cate_list);
		
		$select_list = M()->query('SELECT scr.*,sc.name FROM '.C("DB_PREFIX").'shop_cates_related AS scr  
			INNER JOIN '.C('DB_PREFIX').'shop_cates sc ON sc.id = scr.sc_id AND sc.type = \''.$type.'\' 
			WHERE scr.cid='.$cid);
		$this->assign('select_list',$select_list);
        $this->display();
    }
	
    function insert()
	{
        $cate_ids =  $_REQUEST['cate_ids'];
        $cate_ids = explode(',',$cate_ids);
		$cid = (int)$_REQUEST['cid'];
		$type = $_REQUEST['type'];
        $model = D('ShopCatesRelated');
		M()->query('DELETE FROM '.C("DB_PREFIX").'shop_cates_related 
				WHERE cid = '.$cid." AND type='$type'");
				
        foreach($cate_ids as $id)
		{
            $data  = array();
            $data['cid'] = $cid;
            $data['sc_id'] = $id;
            $data['type'] = $type;
			M()->query('DELETE FROM '.C("DB_PREFIX").'shop_cates_related 
				WHERE cid <> '.$cid." AND sc_id = '$id' AND type='$type'");
			
            $data['sort'] = 100;
			$model->add($data);
        }
		
        $this->assign('jumpUrl', U('ShopCatesRelated/index', array('cid'=>$cid)) );
        $this->success (L('EDIT_SUCCESS'));
    }
    public function remove()
    {
		//删除指定记录
		$result = array('isErr'=>0,'content'=>'');
		$gl_id = $_REQUEST['id'];
		if(!empty($gl_id))
		{
			$model = D("ShopCatesRelated");
			$condition = array('id' => array('in',explode (',',$gl_id)));
			$model->where ( $condition )->delete();
			if(false !== $model->where ( $condition )->delete())
			{
				$this->saveLog(1,$gl_id);
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
