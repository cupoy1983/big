<?php 
include "base.php";
require_once FANWE_ROOT."sdks/renren/renren.func.php";
$appid = $_FANWE['cache']['logins']['renren']['app_key'];
$appkey = $_FANWE['cache']['logins']['renren']['app_secret'];
$code = $_FANWE['request']['code'];
$access_token_obj = getRenrenAccessToken($appid,$appkey,$code);
$access_token_arr = (array)$access_token_obj;
$user = $access_token_arr['user'];
$user['access_token'] = $access_token_arr['access_token'];
require_once FANWE_ROOT."core/class/user/renren.class.php";

$renren = new RenrenUser();
switch($callback_type)
{
	case 'login':
		$renren->loginHandler($user);
		$url = FU('u/index');
	break;
	
	case 'bind':
		$renren->bindHandler($user);
		$url = FU('settings/bind');
	break;
}

fSetCookie('callback_type','');
fHeader("location:".$url);
?>
