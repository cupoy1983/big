<?php
/**
 * 获取商品评论
 */
function getGoodsComment($id)
{
	global $_FANWE;
	$goods = FS('Goods')->getById($id);
	
	if(!$goods)
		return '';
	
	$today_time = getTodayTime();
	if($goods['comment_collect_time'] < $today_time)
	{
		FS('Delay')->create(array('m'=>'goods','a'=>'comment','id'=>$id));
	}
	
	$args = FS('Goods')->getCommentList($id);
	$args['id'] = $id;
	return tplFetch('inc/note/goods_comment',$args,'');
}
?>