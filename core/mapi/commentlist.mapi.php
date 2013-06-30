<?php
class commentlistMapi
{
	public function run()
	{
		global $_FANWE;	
		
		$root = array();
		$root['return'] = 1;

		$share_id = (int)$_FANWE['requestData']['share_id'];
		$goods_id = (int)$_FANWE['requestData']['goods_id'];
		$page = (int)$_FANWE['requestData']['page'];
		$page = max(1,$page);
		
		$sql_count = "SELECT COUNT(DISTINCT comment_id) FROM ".FDB::table("share_comment")." WHERE share_id = ".$share_id;
		$total = FDB::resultFirst($sql_count);
		$page_size = PAGE_SIZE;
		
		$page_total = ceil($total/$page_size);
		if($page > $page_total)
			$page = $page_total;

		$list = array();
		if($page > 0)
		{
			$limit = (($page - 1) * $page_size).",".$page_size;
			$sql = 'SELECT c.*,u.user_name,u.avatar FROM '.FDB::table('share_comment').' AS c 
				INNER JOIN '.FDB::table('user').' AS u ON u.uid = c.uid 
				WHERE c.share_id = '.$share_id.' ORDER BY c.comment_id DESC LIMIT '.$limit;
			$res = FDB::query($sql);
			$list = array();
			while($item = FDB::fetch($res))
			{
				$item['user_avatar'] = avatar($item['avatar'],'m',true);
				$item['time'] = getBeforeTimelag($item['create_time']);
				m_express($item,$item['content']);
				$list[] = $item;
			}
		}

		$root['share_comments']['list'] = $list;
		$root['share_comments']['page'] = array("page"=>$page,"page_total"=>$page_total);
		
		$root['goods_comments']['list'] = array();
		$root['goods_comments']['page'] = array("page"=>0,"page_total"=>0);

		if($goods_id > 0)
		{
			$goods_comments = FS('Goods')->getCommentList($goods_id);
			if(count($goods_comments['list']) > 0)
			{
				$root['goods_comments']['list'] = $goods_comments['list'];
				$root['goods_comments']['page'] = array("page"=>max(1,$page),"page_total"=>$goods_comments['pager']['page_count']);
			}
		}

		m_display($root);
	}
}
?>