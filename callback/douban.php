<?php 
include "base.php";
require_once FANWE_ROOT."sdks/douban/douban.func.php";
$app_key = $_FANWE['cache']['logins']['douban']['app_key'];
$app_secret = $_FANWE['cache']['logins']['douban']['app_secret'];
$code = $_FANWE['request']['code'];
$access_token = getDoubanAccessToken($app_key,$app_secret,$code);

$user = getDoubanUserInfo($access_token);
$user['access_token'] = $access_token;
require_once FANWE_ROOT."core/class/user/douban.class.php";

$douban = new DoubanUser();
switch($callback_type)
{
	case 'login':
		$douban->loginHandler($user);
		$url = FU('u/index');
	break;
	
	case 'bind':
		$douban->bindHandler($user);
		$url = FU('settings/bind');
	break;
}

fSetCookie('callback_type','');
fHeader("location:".$url);
?>
