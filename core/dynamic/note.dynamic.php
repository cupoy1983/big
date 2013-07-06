<?php
/**
 * 获取商品评论
 */
function getGoodsComment($goodShare){
	global $_FANWE;
	$gs = explode("||", $goodShare);
	$goods_id = $gs[0];
	$share_id = $gs[1];
	$goods = FS('Goods')->getById($goods_id);
	$share = FS('Share')->getShareById($share_id);
	if(! $goods){
		return '';
	}
	
	$today_time = getTodayTime();
	//frankie 分享评论采集的最大值为20
	if($goods['comment_collect_time'] < $today_time && $share['comment_count'] < 20){
		FS('Delay')->create(array('m' => 'goods', 'a' => 'comment', 'id' => $share_id));
	}
	//  modified by frankie 去除显示商品评论，改为分享评论
	// 	$args = FS('Goods')->getCommentList($goods_id);
	// 	$args['id'] = $goods_id;
	// 	return tplFetch('inc/note/goods_comment',$args,'');
}
?>