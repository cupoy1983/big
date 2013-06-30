<?php
function bindCacheShop()
{
	$list = array();
	$res = FDB::query("SELECT * FROM ".FDB::table('shop_category')." 
		ORDER BY sort ASC,id ASC");
	while($data = FDB::fetch($res))
	{
		$data['url'] = FU('shop/index',array('cid'=>$data['id']));
		$list['all'][$data['id']] = $data;
	}
	
	$list['root'] = array();
	foreach($list['all'] as $cate)
	{
		if($cate['parent_id'] > 0)
			$list['all'][$cate['parent_id']]['childs'][] = $cate['id'];
		else
			$list['root'][] = $cate['id'];
	}
	
	FanweService::instance()->cache->saveCache('shops', $list);

	$relateds = array();
	$res = FDB::query("SELECT * FROM ".FDB::table('shop_cates_related'));
	while($data = FDB::fetch($res))
	{
		if(isset($list['all'][$data['cid']]))
			$relateds[$data['type']][$data['sc_id']] = $data['cid'];
	}
	
	foreach($relateds as $key => $val)
	{
		FanweService::instance()->cache->saveCache('shop_cate_related_'.$key, $val);
	}
}
?>