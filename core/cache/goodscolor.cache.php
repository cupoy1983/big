<?php
function bindCacheGoodscolor()
{
	$colors = array();
	$res = FDB::query("SELECT * FROM ".FDB::table('goods_color')." WHERE status = 1 ORDER BY sort ASC,id ASC");
	while($data = FDB::fetch($res))
	{
		$colors[$data['id']] = $data;
	}
	
	FanweService::instance()->cache->saveCache('goods_color', $colors);
}
?>