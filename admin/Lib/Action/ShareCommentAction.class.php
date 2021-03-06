<?php
/**
 * 分享评论管理
 */
class ShareCommentAction extends CommonAction{
    public function index() {
        //搜索条件、分页信息
        $model = M();
        $uname = trim($_REQUEST['uname']);
        $keyword = trim($_REQUEST['keyword']);
        $where_arr = array();
        if(!empty($uname))
        {
                $this->assign("uname",$uname);
                $parameter['uname'] = $uname;
                $uid = (int)D('User')->where("user_name = '".$uname."'")->getField('uid');
                if($uid > 0)
                        $where_arr[] = " sc.uid = ".$uid;
        }
        if(!empty($keyword)){
            $this->assign('keyword',$keyword);
            $parameter['keyword'] = $keyword;
            $where_arr[] = ' sc.content like \'%'.$keyword.'%\' ';
        }

        if($where_arr){
            $where = ' WHERE '.implode(' AND ', $where_arr);
            
        }

        //获取评论列表
         $sql = 'SELECT COUNT(DISTINCT sc.comment_id) AS pcount 
                FROM '.C("DB_PREFIX").'share_comment AS sc 
                LEFT JOIN '.C("DB_PREFIX").'user AS u ON u.uid = sc.uid 
                '.$where;

        $count = $model->query($sql);
        $count = $count[0]['pcount'];

        $sql = 'SELECT sc.comment_id,LEFT(sc.content,80) AS content,u.user_name,sc.create_time,sc.share_id  
                FROM '.C("DB_PREFIX").'share_comment AS sc 
                LEFT JOIN '.C("DB_PREFIX").'user AS u ON u.uid = sc.uid 
                '.$where.' GROUP BY sc.comment_id';

        $this->_sqlList($model,$sql,$count,$parameter,'sc.comment_id',false,'returnUrl1');

        $this->display ();
        return;
        
    }
    
    public function removeComment(){
    	Vendor("common");
    	//删除指定记录
		$result = array('isErr'=>0,'content'=>'');
		$id = $_REQUEST['id'];
		if(!empty($id)){
			$res = FDB::query("SELECT comment_id, share_id FROM ".FDB::table('share_comment')." WHERE comment_id IN (".$id.")");
			while($c = FDB::fetch($res)){
				$commentId = $c['comment_id'];
				$shareId = $c['share_id'];
				
				$model = M('ShareComment');
				$pk = 'comment_id';
				
				if(false !== $model->where($pk.' = '. $commentId)->delete()){
					D('CommentMe')->where($pk.' = '. $commentId)->delete();
					$count = $model->where( 'share_id ='. $shareId)->count();
					$key = getDirsById($shareId);
					clearCacheDir('share/'.$key.'/commentlist');
					D('Share')->where("share_id = '$shareId'")->setField('comment_count',$count);
					FS('Share')->updateShareCache($shareId,'comments');
				}else{
					$this->saveLog(0,$id);
					$result['isErr'] = 1;
					$result['content'] = L('REMOVE_ERROR');
					die(json_encode($result));
				}
			}
			$this->saveLog(1,$id);
		}else{
			$result['isErr'] = 1;
			$result['content'] = L('ACCESS_DENIED');
		}

		die(json_encode($result));
	}
        
	public function editComment(){
		$model = D('ShareComment');
		Cookie::set ( '_currentUrl_',NULL );
		$id = $_REQUEST [$model->getPk ()];
		$vo = $model->getById($id);

		$this->assign ( 'vo', $vo );
		$this->display ();
	}
	
    public function updateComment(){
		$model = D('ShareComment');
		if (false === $data = $model->create ()) {
			$this->error ( $model->getError () );
		}
		// 更新数据
		$list=$model->save ();
		$id = $data['comment_id'];
		if (false !== $list) {
			//成功提示
			Vendor("common");
			$share_id = D('ShareComment')->where("comment_id = '$id'")->getField('share_id');
			$key = getDirsById($share_id);
			clearCacheDir('share/'.$key.'/commentlist');
			$this->saveLog(1,$id);
			$this->assign ( 'jumpUrl', Cookie::get ( '_currentUrl_' ) );
			$this->success (L('EDIT_SUCCESS'));
		} else {
			//错误提示
			$this->saveLog(0,$id);
			$this->error (L('EDIT_ERROR'));
		}
	}
}
?>
