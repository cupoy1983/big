<?php
function bindCacheSeo()
{
	$res = FDB::query('SELECT * FROM '.FDB::table('seo'));
	while($item = FDB::fetch($res))
	{
		$id = $item['id'];
		$item['rewrite'] = unserialize($item['rewrite']);
		
		$module = $item['rewrite']['module'];
		$action = str_replace($item['type'].'_','',$item['key']);
		$modules[$item['type']]['module'] = $module;
		$modules[$item['type']][$action]['action'] = $item['rewrite']['action'];
		$modules[$item['type']][$action]['seo']['title'] = array();
		$temps = explode("\n",$item['title']);
		foreach($temps as $temp)
		{
			$temp = trim($temp);
			if(!empty($temp))
			{
				preg_match_all("/\{(.+?)\}/",$temp,$match);
				$modules[$item['type']][$action]['seo']['title'][] = array('args' => $match[1],'content' => $temp);
			}
		}
		
		$modules[$item['type']][$action]['seo']['keywords'] = array();
		$temps = explode("\n",$item['keywords']);
		foreach($temps as $temp)
		{
			$temp = trim($temp);
			if(!empty($temp))
			{
				preg_match_all("/\{(.+?)\}/",$temp,$match);
				$modules[$item['type']][$action]['seo']['keywords'][] = array('args' => $match[1],'content' => $temp);
			}
		}
		
		$modules[$item['type']][$action]['seo']['description'] = array();
		$temps = explode("\n",$item['description']);
		foreach($temps as $temp)
		{
			$temp = trim($temp);
			if(!empty($temp))
			{
				preg_match_all("/\{(.+?)\}/",$temp,$match);
				$modules[$item['type']][$action]['seo']['description'][] = array('args' => $match[1],'content' => $temp);
			}
		}
	}
	FanweService::instance()->cache->saveCache('seos', $modules);
}
?>