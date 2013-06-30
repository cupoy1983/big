<?php 
include "base.php";
require_once FANWE_ROOT."sdks/kaixin/kaixin.func.php";
$app_key = $_FANWE['cache']['logins']['kaixin']['app_key'];
$app_secret = $_FANWE['cache']['logins']['kaixin']['app_secret'];
$code = $_FANWE['request']['code'];
$access_token = getKaixinAccessToken($app_key,$app_secret,$code);
$user = getKaixinUserInfo($access_token);
$user['access_token'] = $access_token;
require_once FANWE_ROOT."core/class/user/kaixin.class.php";

$kaixin = new KaixinUser();
switch($callback_type)
{
	case 'login':
		$kaixin->loginHandler($user);
		$url = FU('u/index');
	break;
	
	case 'bind':
		$kaixin->bindHandler($user);
		$url = FU('settings/bind');
	break;
}

fSetCookie('callback_type','');
fHeader("location:".$url);
?>
