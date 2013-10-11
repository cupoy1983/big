<?php
/**
 * 达人采集类
 * @author frankie
 *
 */
class DarenCollectionAction extends CommonAction{
	
	public function index(){
		$nick = $_REQUEST['nick'];
		$pid = $_REQUEST['pid'];
		if(!empty($nick) && !empty($pid)){
			$where = "nick='".$nick."' AND partner_id=".$pid;
		}
		
		$model = M("DarenCollection");
		$count = $model->where($where)->count();
		
		// 建立分页
		import("ORG.Util.Page");
		$p = new Page($count, 10);
		$page = $p->show();
		
		// 当前页数据查询
		$list = $model->where($where)->limit($p->firstRow.','.$p->listRows)->select();
		vendor('common');
		foreach($list as $k => $v){
			$list[$k]["partner"] = FS("Collect")->getPartnerById($v["partner_id"]);
		}
		$this->assign('page', $page);
		$this->assign('list', $list);
		
		$this->display();
	}
	
	public function insert(){
		$_POST["gmt_create"] = gmtTime();
		$_POST["gmt_modified"] = gmtTime();
		$_POST["nick"] = trim($_POST["nick"]);
		$pid = $_POST["partner_id"];
		
		$model = M("DarenCollection");
		$count = $model->where("nick='".$_POST["nick"]."' AND partner_id=".$pid)->count();
		if($count > 0){
			$this->error("该用户已存在！");
		}
		
		parent::insert();
	}
	
	public function albumCollect(){
		vendor('common');
		
		$id = (int)$_REQUEST['id'];
		$page =  (int)$_REQUEST['page'];
		if(empty($page)){
			$page = 1;
		}

		$tips = '开始获取第 '.$page.' 页';
		$this->assign("tips",$tips);
		
		ob_start();
		ob_end_flush(); 
		ob_implicit_flush(1);
		$this->display();
		
		$model = M("DarenCollection");
// 		$count = $model->count();
		
		$user = $model->where("id=".$id)->find();
		$continue = FS("Mogujie")->collectDarenAlbumInfo($user["url"]."/".$page."/own/new", $user["uid"]);
		
		if($continue){
			echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('DarenCollection/albumCollect',array('id'=>$id,'page'=>$page + 1)).'";},1000);</script>');
		}
		elseif(!$continue && $page >1){
			echoFlush('<script type="text/javascript">setTimeout(function(){$("#notice").html($("#notice").html() + "<br/>采集完成");},500);</script>');
			echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('DarenCollection/index').'";},1500);</script>');
		}else{
			echoFlush('<script type="text/javascript">setTimeout(function(){$("#notice").html($("#notice").html() + "<br/>API返回数据错误");},500);</script>');
			echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('DarenCollection/index').'";},1500);</script>');
		}
	}
	
	public function albumList(){
		vendor('common');
		
		$nick = $_REQUEST['nick'];
		$pid = $_REQUEST['pid'];
		
		if(!empty($nick) && !empty($pid)){
			$dr = M("DarenCollection")->where("nick='".$nick."' AND partner_id=".$pid)->find();
			$uid = $dr["uid"];
		}else{
			$uid = 1;
		}
		$where = "uid=".$uid;
		$model = M("AlbumCollection");
		$count = $model->where($where)->count();
		
		// 建立分页
		import("ORG.Util.Page");
		$p = new Page($count, 10);
		$page = $p->show();
		
		// 当前页数据查询
		$list = $model->where($where)->limit($p->firstRow.','.$p->listRows)->select();
		
		foreach($list as $k => $v){
			$list[$k]["partner"] = FS("Collect")->getPartnerById($v["partner_id"]);
		}
		
		$this->assign('count', $count);
		$this->assign('uid', $uid);
		$this->assign('nick', $nick);
		$this->assign('page', $page);
		$this->assign('list', $list);
		
		$this->display();
		return;
	}
	
	public function itemsCollect(){
		vendor('common');
	
		$uid = (int)$_REQUEST['uid'];
		$page =  (int)$_REQUEST['page'];
		$cPage =  (int)$_REQUEST['cPage'];
		if(empty($uid)){
			$this->error("该用户不存在！");
		}
		if(empty($page)){
			$page = 1;
			$cPage = 1;
		}
	
		$tips = '开始获取第 '.$page.' 个专辑中的第'.$cPage.' 个分页';
		$this->assign("tips",$tips);
	
		ob_start();
		ob_end_flush();
		ob_implicit_flush(1);
		$this->display();
	
		$where = "uid=".$uid;
		$model = M("AlbumCollection");
		
		// 当前页数据查询
		$list = $model->where($where)->limit(($page - 1).',1')->order('gmt_modified asc')->select();
		if($page==1 && empty($list)){
			$this->error("该用户无可采集专辑！");
		}elseif(empty($list)){
			$model = M("DarenCollection");
			$user = $model->where("uid=".$uid)->find();
			echoFlush('<script type="text/javascript">setTimeout(function(){$("#notice").html($("#notice").html() + "<br/>采集完成");},500);</script>');
			echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('DarenCollection/albumList', 'nick='.$user["nick"].'&pid='.$user["partner_id"]).'";},1500);</script>');
			return true;
		}
		
		foreach($list as $album){
			$next = FS("Mogujie")->collectAlbumItemInfo($album["url"]."/icon/".$cPage, $album["uid"], $album["album_id"]);
		}
		
		//该专辑中有下一页，在该page下cPage+1
		//该专辑中无下一页，page+1,cPage置零
		if($next){
			echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('DarenCollection/itemsCollect',array('uid'=>$uid,'page'=>$page, 'cPage'=>$cPage + 1)).'";},5000);</script>');
		}else{
			echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('DarenCollection/itemsCollect',array('uid'=>$uid,'page'=>$page + 1, 'cPage'=>1)).'";},8000);</script>');
		}
	}
}

?>