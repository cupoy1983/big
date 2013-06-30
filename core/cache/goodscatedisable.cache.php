<?php
function bindCacheGoodscatedisable()
{
	$list = array();
	$res = FDB::query("SELECT * FROM ".FDB::table('goods_cate_disable'));
	while($data = FDB::fetch($res))
	{
		$list[$data['type']][$data['id']] = 1;
	}
	
	foreach($list as $type => $data)
	{
		FanweService::instance()->cache->saveCache('goods_cate_disable_'.$type, $data);
	}
}
?>