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
class ActivityPostAction extends CommonAction
{
	public function index()
	{
		if(isset($_REQUEST['aid']))
			$aid = intval($_REQUEST['aid']);
		else
			$aid = intval($_SESSION['activity_post_tid']);
		
		$_SESSION['activity_post_tid'] = $aid;
		
		$where = 'WHERE ap.aid = ' . $aid;
		$parameter = array();
		$uname = trim($_REQUEST['uname']);
		
		$is_empty = false;
		if(!empty($uname))
		{
			$this->assign("uname",$uname);
			$parameter['uname'] = $uname;
			$uid = (int)D('User')->where("user_name = '".$uname."'")->getField('uid');
			if($uid == 0)
				$is_empty = true;
			else
				$where.=" AND ap.uid = ".$uid;
		}
		
		if(!$is_empty)
		{
			$model = M();
			
			$sql = 'SELECT COUNT(DISTINCT ap.id) AS pcount 
				FROM '.C("DB_PREFIX").'activity_post AS ap '.$where;
			
			$count = $model->query($sql);
			$count = $count[0]['pcount'];
	
			$sql = 'SELECT ap.id,LEFT(ap.content,80) AS content,u.user_name,ap.create_time,ap.share_id  
				FROM '.C("DB_PREFIX").'activity_post AS ap 
				LEFT JOIN '.C("DB_PREFIX").'user AS u ON u.uid = ap.uid 
				'.$where.' GROUP BY ap.id';
			$this->_sqlList($model,$sql,$count,$parameter,'ap.id',false,'returnUrl1');
		}
		$this->display ();
		return;
	}

    public function update()
    {
        Vendor("common");
        $id = intval($_REQUEST['id']);
        $share_id = D('ActivityPost')->where("id = '$id'")->getField('share_id');
        if($share_id > 0)
        {
            $content = trim($_REQUEST['content']);
            FS("Share")->updateShare($share_id,'',$content);
        }
        parent::update();
    }

	public function remove()
	{
		//删除指定记录
		Vendor("common");
		$result = array('isErr'=>0,'content'=>'');
		$id = $_REQUEST['id'];
		if(!empty($id))
		{
			$name=$this->getActionName();
			$model = D($name);
			$pk = $model->getPk ();
			$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
			$count = $model->where ( $condition )->count();
			$res = $model->where ( $condition )->select();

			foreach($res as $item)
			{
				$share_id = intval($item['share_id']);
                FS("Activity")->deletePost($share_id);
			}

			$this->saveLog(1,$id);
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
		Cookie::set ( '_currentUrl_',NULL );
		parent::edit();
	}
}

?>