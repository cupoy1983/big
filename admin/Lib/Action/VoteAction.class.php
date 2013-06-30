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
class VoteAction extends CommonAction
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
				$where.=" AND v.uid = ".$uid;
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
			$sql_count = 'SELECT COUNT(v.id) AS gcount FROM '.C("DB_PREFIX").'vote AS v ';
			$sql = 'SELECT v.*,u.user_name FROM '.C("DB_PREFIX").'vote AS v ';
			if($is_match)
			{
				$sql_count = 'SELECT COUNT(vm.id) AS gcount FROM '.C("DB_PREFIX").'vote_match AS vm ';
				$sql = 'SELECT v.*,u.user_name FROM '.C("DB_PREFIX").'vote_match AS vm ';
				$append_sql = 'INNER JOIN '.C("DB_PREFIX").'vote AS v ON v.id = vm.id ';
				$sql_count .= $append_sql;
				$sql .= $append_sql;
				$where.=" AND match(vm.content) against('".$match_key."' IN BOOLEAN MODE) ";
			}

			$sql .= ' LEFT JOIN '.C("DB_PREFIX").'user AS u ON u.uid = v.uid ';
		
			if(!empty($where))
				$where = str_replace('WHERE AND','WHERE','WHERE'.$where);

			$sql_count .= $where;
			$sql .= $where;
			
			$count = $model->query($sql_count);
			$count = $count[0]['gcount'];

			$this->_sqlList($model,$sql,$count,$parameter,'v.id');	
		}

		$this->display();
	}

	public function edit()
	{
		vendor("common");
		$id = intval($_REQUEST['id']);

		$vo = D("Vote")->getById($id);
		$vo['share'] = FS('Share')->getShareDetail($vo['share_id']);
		$this->assign ('vo',$vo);
		$vote_options = FS('Vote')->getOptions($id,$vo['num']);
		$this->assign ('vote_options',$vote_options);
		$user_name = D("User")->where('uid = '.$vo['uid'])->getField('user_name');
		$this->assign ('user_name',$user_name);
		
		$this->display();
	}
	
	public function update()
	{
		vendor("common");
		$id = intval($_REQUEST['id']);
		$vote = FS('Vote')->getById($id);
		
		if(empty($vote))
			$this->redirect('vote/index');
		
		FS("Vote")->update($id,$_REQUEST);
		
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
				FS('Vote')->delete($id);
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
	$count = D('VotePost')->where('vid = '.(int)$id)->count();
	if($count > 0)
		return "(".$count.")&nbsp;&nbsp; <a href='".u("VotePost/index",array("vid"=>$id))."'>".l("CHECK_REPLY")."</a>";
	else
		return $count;
}
?>