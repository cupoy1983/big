<?php
class ExchangeModule
{
	public function index()
	{
		global $_FANWE;
		$page = $_FANWE['page'];
		$order = ' ORDER BY sort ASC,end_time ASC,id DESC';
		$where = ' WHERE status = 1 AND (begin_time < '.TIME_UTC.' OR begin_time = 0) AND (end_time > '.TIME_UTC.' OR end_time = 0)';
		if($page == 1)
		{
			$notice_list = array();
			$best_ids = array();
			$sql = 'SELECT * FROM '.FDB::table('exchange_goods').' WHERE status = 1 AND begin_time > '.TIME_UTC.$order;
			$res = FDB::query($sql);
			while($data = FDB::fetch($res))
			{
				$data['price_format'] = priceFormat($data['price']);
				$notice_list[] = $data;
				$best_ids[] = $data['id'];
			}
			
			$best_list = array();
			$sql = 'SELECT * FROM '.FDB::table('exchange_goods').' WHERE status = 1 AND is_best = 1 AND (begin_time < '.TIME_UTC.' OR begin_time = 0) AND (end_time > '.TIME_UTC.' OR end_time = 0)'.$order;
			$res = FDB::query($sql);
			while($data = FDB::fetch($res))
			{
				$data['num'] = $data['stock'] - $data['buy_count'];
				$data['url'] = FU('exchange/info',array('id'=>$data['id']));
				$data['price_format'] = priceFormat($data['price']);
				if($data['end_time'] > 0)
					$data['end_time_format'] = getEndTimelag($data['end_time']);
				$best_list[] = $data;
				$best_ids[] = $data['id'];
			}
			
			if(count($best_ids) > 0)
			{
				$best_ids = implode(',',$best_ids);
				$where.= ' AND id NOT IN ('.$best_ids.')';
			}
		}
		
		$sql = 'SELECT COUNT(id) FROM '.FDB::table('exchange_goods').$where;
		$goods_count = FDB::resultFirst($sql);
		
		$page_size = 10;
		$pager = buildPage('exchange/index',array(),$goods_count,$_FANWE['page'],$page_size);
		
		$sql = 'SELECT * FROM '.FDB::table('exchange_goods').$where.$order.' LIMIT '.$pager['limit'];
		$goods_list = array();
		$res = FDB::query($sql);
		while($data = FDB::fetch($res))
		{
			$data['apply_user'] = fStripslashes(unserialize($data['apply_cache']));
			unset($data['apply_cache']);
			$data['price_format'] = '¥'.floatval(round($data['price'] * 100) / 100);
			$data['num'] = $data['stock'] - $data['buy_count'];
			$data['apply_count'] = $data['apply_count'] + $data['buy_count'];
			$data['url'] = FU('exchange/info',array('id'=>$data['id']));
			$goods_list[] = $data;
		}
		
		$apply_list = FS("Exchange")->getApplyTop();
		$order_list = FS("Exchange")->getOrderTop();
		$score_list = FS("Exchange")->getScoreTop();
		$exchange_list = FS("Exchange")->getExchangeTop();
		
		if($_FANWE['uid'] > 0)
			$consignee = FDB::fetchFirst('SELECT * FROM '.FDB::table('user_consignee').' WHERE uid = '.$_FANWE['uid']);
		
		include template('page/exchange/exchange_index');
		display();
	}
	
	public function rule()
	{
		global $_FANWE;
		$title = sprintf(lang('common','user_sore_rule'),$_FANWE['setting']['site_name']);
		$_FANWE['nav_title'] = $title;
		
		$cache_file = getTplCache('page/exchange/exchange_rule');
		if(!@include($cache_file))
		{
			include template('page/exchange/exchange_rule');
		}
		display($cache_file);
	}
	
	public function info()
	{
        global $_FANWE;
        $id = $_FANWE['request']['id'];
        $sql = 'SELECT * FROM ' . FDB::table('exchange_goods') . ' where id =' . $id;
        $info = FDB::fetchFirst($sql);
		unset($data['apply_cache']);
		$info['price_format'] = '¥'.floatval(round($info['price'] * 100) / 100);
		$info['num'] = $info['stock'] - $info['buy_count'];
		$info['apply_count'] = $info['apply_count'] + $info['buy_count'];

		$_FANWE['PAGE_SEO_SELF'] = $info;
		
		$bu_share_img = getImgById($info['img_id'],0,0,0,1);
		
		$apply_user = array();
		$sql = 'SELECT o.uid,u.user_name,u.avatar 
				FROM '.FDB::table('order').' AS o 
				INNER JOIN '.FDB::table('user').' AS u ON u.uid = o.uid 
				WHERE o.rec_id = '.$id.' 
				ORDER BY o.id DESC LIMIT 0,100';
		
		$query = FDB::query($sql);
		while($data = FDB::fetch($query))
		{
			$apply_user[] = $data;
		}
		
		$apply_list = FS("Exchange")->getApplyTop();
		$order_list = FS("Exchange")->getOrderTop();
		$score_list = FS("Exchange")->getScoreTop();
		$exchange_list = FS("Exchange")->getExchangeTop();
		
		$_FANWE['nav_title'] = $info['name'].'-'.lang('common','activity');
		
		$page_args = array(
			'id'=>$id
		);

		$count = (int)FDB::resultFirst('SELECT COUNT(id) FROM '.FDB::table('exchange_post').' WHERE eid = '.$id);
		$pager = buildPage('exchange/info',$page_args,$count,$_FANWE['page'],10);
		$post_list = FS('Exchange')->getPostList($id,$pager['limit']);
		
		$args = array(
			'share_list'=>&$post_list,
			'pager'=>&$pager,
			'current_share_id'=>$activity['share_id']
		);
		$post_html = tplFetch("inc/share/post_share_list",$args);
		
        include template('page/exchange/exchange_info');
        display();
    }
}
?>