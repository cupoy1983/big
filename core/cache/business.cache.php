<?php
function bindCacheBusiness()
{
	$list = array();
	$domains = array();
	$res = FDB::query("SELECT * FROM ".FDB::table('sharegoods_module')." 
		WHERE status = 1 ORDER BY sort ASC,id ASC");
	while($data = FDB::fetch($res))
	{
		if($api_data  = @unserialize($data['api_data']))
		{
			$data = array_merge($data,$api_data);
		}
		unset($data['api_data']);
		$domains[] = getBusinessUrlRootDomain($data['url']);
		$list[$data['class']] = $data;
	}
	
	$res = FDB::query("SELECT * FROM ".FDB::table('yiqifa_shop')." 
		WHERE status = 1 ORDER BY sort ASC,id ASC");
	while($data = FDB::fetch($res))
	{
		$domain = getBusinessUrlRootDomain($data['url']);
		if(!in_array($domain,$domains))
		{
			if(!empty($data['icon']))
				$data['icon'] = './public/upload/business/'.$data['icon'];
			$list['yiqifa'.$data['id']] = $data;
		}
	}
	
	FanweService::instance()->cache->saveCache('business', $list);
	
	/*$list = array();
	foreach($domains as $domain)
	{
		$domain = explode(',',$domain);
		foreach($domain as $item)
		{
			$item = parse_url($item);
			if(!empty($item['host']))
				$list[] = $item['host'];
		}
	}
	
	$js = file_get_contents(FANWE_ROOT.'shou/shou_source.js');
	$js = str_replace('#GOODS_DOMAINS#',getJson($list),$js);
	file_put_contents(FANWE_ROOT.'shou/shou.js',$js);*/
}

function getBusinessUrlRootDomain($url)
{
	$urls= parse_url($url);
	if(empty($urls['scheme']) || empty($urls['host']))
		return false;
	
	$host = explode('.',$urls['host']);
	array_shift($host);
	return implode('.',$host);
}
?>