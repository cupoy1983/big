<?php
$args = unserialize(REQUEST_ARGS);
$time = time();
if(!isset($args['isloop']) || (int)$args['isloop'] == 0)
{
	$send_time = (int)@file_get_contents(ROOT_PATH.'public/records/sync_send.time.php');
	if($time - $send_time < 300)
		exit;
}
@file_put_contents(ROOT_PATH.'public/records/sync_send.time.php',$time);

require ROOT_PATH.'core/fanwe.php';
$fanwe = &FanweService::instance();
$fanwe->is_session = false;
$fanwe->is_user = false;
$fanwe->is_cron = false;
$fanwe->is_misc = false;
$fanwe->cache_list = array('logins');
$fanwe->initialize();

$_FANWE['request'] = unserialize(REQUEST_ARGS);
$weibo = FDB::fetchFirst("SELECT * FROM ".FDB::table("pub_schedule")." ORDER BY id ASC LIMIT 0,1");
if($weibo)
{
	$query = FDB::query("DELETE FROM ".FDB::table("pub_schedule")." WHERE id = ".$weibo['id']);
	if($query !== FALSE && FDB::affectedRows() > 0)
	{
		if(file_exists(FANWE_ROOT."login/".$weibo['type'].".php"))
		{
			require_once FANWE_ROOT."login/".$weibo['type'].".php";
			$class =$weibo['type'];
			$mods[$weibo['type']] = new $class;
	
			$data = unserialize(base64_decode($weibo['data']));
			$data['uid'] = $weibo['uid'];
			$mods[$weibo['type']]->sendMessage($data);
		}
		$args = array('m'=>'share','a'=>'sync_send','isloop'=>1);
		FS('Cron')->createRequest($args);
	}
}
?>
