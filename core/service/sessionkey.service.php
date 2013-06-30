<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

/**  
 * commission.service.php
 *
 * taobao客session key
 *
 * @package class
 * @author jobinlin <jobin.lin@gmail.com>
 */
class SessionkeyService
{
	public function runCron($crons)
	{
		$cron = array();
		$cron['server'] = 'sessionkey';
		$cron['run_time'] = TIME_UTC + 43200;
		FDB::insert('cron',$cron);
		SessionkeyService::checkSessionkey();
	}
    
	public function checkSessionkey()
	{
		$session_data = @include FANWE_ROOT.'public/taobao/sessionkey.php';
		if($session_data)
		{
			require_once FANWE_ROOT."sdks/taobao/taobao.func.php";
			$result = FDB::fetchFirst('SELECT api_data FROM '.FDB::table('sharegoods_module').' WHERE class=\'taobao\'');
			$api_data = unserialize($result['api_data']);

			$url = GetTaoBaoRefreshTokenUrl($api_data['app_key'],$api_data['app_secret'],$api_data['session_key'],$session_data['refresh_token']);
                        //更新session key 
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
			curl_setopt($ch, CURLOPT_REFERER,_REFERER_);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$content = curl_exec($ch);
			curl_close($ch);
			$sessionkey_data = json_decode($content,true);
                        
			//更新缓存字段
			$api_data['expires_in'] = TIME_UTC + (int)$sessionkey_data['expires_in'];
			FDB::query('UPDATE '.FDB::table('sharegoods_module').' set api_data = \''.serialize($api_data).'\' WHERE class=\'taobao\'');
			
			$sessionkey_data = '<? return '.var_export($sessionkey_data,true).';?>';
			file_put_contents(FANWE_ROOT.'public/taobao/sessionkey.php',$sessionkey_data);
		}
	}
}
?>