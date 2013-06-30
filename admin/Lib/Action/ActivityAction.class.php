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
class ActivityAction extends CommonAction
{
	public function index()
	{
		$where = '';
		$parameter = array();
		$keyword = trim($_REQUEST['keyword']);
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
				$where.=" AND a.uid = ".$uid;
		}
		
		if(!$is_empty)
		{
			$is_match = false;
			if(!empty($keyword))
			{
				vendor("common");
				$this->assign("keyword",$keyword);
				$parameter['keyword'] = $keyword;
				$match_key = FS('Words')->segmentToUnicode($keyword,'+');
				$is_match = true;
			}

			$model = M();
			
			$append_sql = '';
			$sql_count = 'SELECT COUNT(a.id) AS gcount FROM '.C("DB_PREFIX").'activity AS a ';
			$sql = 'SELECT a.*,u.user_name FROM '.C("DB_PREFIX").'activity AS a ';
			if($is_match)
			{
				$sql_count = 'SELECT COUNT(am.id) AS gcount FROM '.C("DB_PREFIX").'activity_match AS am ';
				$sql = 'SELECT a.*,u.user_name FROM '.C("DB_PREFIX").'activity_match AS am ';
				$append_sql = 'INNER JOIN '.C("DB_PREFIX").'activity AS a ON a.id = am.id ';
				$sql_count .= $append_sql;
				$sql .= $append_sql;
				$where.=" AND match(am.content) against('".$match_key."' IN BOOLEAN MODE) ";
			}

			$sql .= ' LEFT JOIN '.C("DB_PREFIX").'user AS u ON u.uid = a.uid ';
		
			if(!empty($where))
				$where = str_replace('WHERE AND','WHERE','WHERE'.$where);

			$sql_count .= $where;
			$sql .= $where;
			
			$count = $model->query($sql_count);
			$count = $count[0]['gcount'];

			$this->_sqlList($model,$sql,$count,$parameter,'a.id');	
		}

		$this->display();
	}

	public function edit()
	{
		vendor("common");
		$id = intval($_REQUEST['id']);

		$vo = D("Activity")->getById($id);
		$vo['share'] = FS('Share')->getShareDetail($vo['share_id']);
		
		$vo['expiration_date'] = '';
		if($vo['expiration_time'] > 0)
			$vo['expiration_date'] = toDate($vo['expiration_time'],'Y-m-d H:i');
		
		$vo['end_date'] = '';
		if($vo['end_time'] == 0)
			$vo['begin_date'] = toDate($vo['begin_time'],'Y-m-d');
		else
		{
			$vo['begin_date'] = toDate($vo['begin_time'],'Y-m-d H:i');
			$vo['end_date'] = toDate($vo['end_time'],'Y-m-d H:i');
		}
		
		$vo['share'] = FS('Share')->getShareDetail($vo['share_id']);
		$activity_fields = explode("\n",trim($vo['fields']));
		$fields_count = 10 - count($activity_fields);
		$this->assign ('activity_fields',$activity_fields);
		$this->assign ('fields_count',$fields_count);
		$this->assign ('vo',$vo);
		
		$cates = D('ActivityCate')->order('sort ASC,id ASC')->select();
		$this->assign ('cates',$cates);
		
		$citys = D('Region')->where('parent_id = 0')->order('sort ASC,id ASC')->select();
		$this->assign ('citys',$citys);
		
		$this->display();
	}
	
	public function update()
	{
		vendor("common");
		$id = intval($_REQUEST['id']);
		$activity = D("Activity")->getById($id);
		
		if(empty($activity))
			$this->redirect('activity/index');
		
		FS("Activity")->update($id,$_REQUEST);
		
		$this->saveLog(1,$id);
		$this->assign('jumpUrl', Cookie::get ( '_currentUrl_' ) );
		$this->success (L('EDIT_SUCCESS'));
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
			
			vendor("common");
			setTimeLimit();
			$ids = explode (',',$id );
			foreach($ids as $id)
			{
				FS('Activity')->delete($id);
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

function getPostCount($pcount,$id)
{
	$count = D('ActivityPost')->where('aid = '.(int)$id)->count();
	if($count > 0)
		return "(".$count.")&nbsp;&nbsp; <a href='".u("ActivityPost/index",array("aid"=>$id))."'>".l("CHECK_REPLY")."</a>";
	else
		return $count;
}
?>