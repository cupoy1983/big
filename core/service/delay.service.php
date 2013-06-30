<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

/**  
 * delay.service.php
 *
 * 延时处理服务
 *
 * @package class
 * @author awfigq <awfigq@qq.com>
 */
class DelayService
{
	public function get($key)
	{
		$result = array('status'=>-1,'data'=>'');
                $cache_path = PUBLIC_ROOT.'./data/caches/custom/'.$key.'.cache.php';
		if(!file_exists($cache_path))
			return $result;
		else
		{
                        $cache_data = @file_get_contents($cache_path);
			if(empty($cache_data))
				return NULL;
			
			$cache_data = @unserialize($cache_data);
			if(empty($cache_data))
				return NULL;
                        
                        $result['data'] = $cache_data['data'];
			if((int)$cache_data['expired_time'] > 0 && TIMESTAMP > (int)$cache_data['expired_time'])
			{
				$result['status'] = 0;
				return $result;
			}
			
			$time_clear = 0;
			$clear_path = FANWE_ROOT.'./public/data/is_clear.lock';
			if(file_exists($clear_path))
				$time_clear = (int)@file_get_contents($clear_path);

			if(($time_clear > 0 && $cache_data['time'] < $time_clear))
			{
				$result['status'] = 0;
				return $result;
			}
			
			$result['status'] = 1;
			return $result;
		}
	}

	public function create($args = array(),$is_script = true)
	{
		global $_FANWE;
		$args['request_time'] = TIMESTAMP;
		$args = serialize($args);
		$authkey = md5($_FANWE['config']['security']['authkey']);
		$args = rawurlencode(authcode($args,'ENCODE',$authkey));
		$args = 'args='.$args;
		
		if($is_script)
		{
			$url = SITE_URL.'services/delay.php?'.$args;
			$_FANWE['delay_scripts'][] = $url;
			return $url;
		}
		else
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$_FANWE['site_url']."services/delay.php");
			curl_setopt($ch, CURLOPT_TIMEOUT,5);
			curl_setopt($ch, CURLOPT_POST, 1 );     
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $args); 
			curl_exec($ch);
			curl_close($ch);
		}
	}
}
?>