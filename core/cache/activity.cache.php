<?php
function bindCacheActivity()
{
	$list = array();
	$res = FDB::query("SELECT * FROM ".FDB::table('activity_cate')." 
		ORDER BY sort ASC,id ASC");
	while($data = FDB::fetch($res))
	{
		$list[$data['id']] = $data;
	}
	
	FanweService::instance()->cache->saveCache('activity_cate', $list);
}
?>