<?php
/*
 * renren
 */
function GetRenrenLoginUrl($top_appkey)
{
	global $_FANWE;
        $state = md5(uniqid(rand(),TRUE));//CSRF protection
	$url = "https://graph.renren.com/oauth/authorize?client_id=".
             $top_appkey."&response_type=code&redirect_uri=".urlencode($_FANWE['site_url']."callback/renren.php")."&display=page&state=".$state;
        //$_FANWE['site_url']."callback/renren.php"
        fSetCookie('renren_state',$state);
        return $url;
}
function getRenrenAccessToken($appid,$appkey,$code)
{
    global $_FANWE;
    $renren_state = $_FANWE['cookie']['renren_state'];
    if($_REQUEST['state'] == $renren_state){

        $token_url = 'https://graph.renren.com/oauth/token?grant_type=authorization_code&client_id='.$appid.'&redirect_uri='. urlencode($_FANWE['site_url']."callback/renren.php").'&client_secret='.$appkey.'&code='.$code;
		$response = file_get_contents($token_url);
        $params = json_decode($response);
        $params = object_array($params);
        return $params;
    }
    else 
    {
        echo("The state does not match. You may be a victim of CSRF.");
    }
    
}
function getRenrenUserInfo($access_token)
{
    $graph_url = "https://api.renren.com/v2/user?access_token=" 
       . $access_token;

    $user = json_decode(file_get_contents($graph_url));

    return $user;
}
function object_array($array)
{
if(is_object($array))
{
$array = (array)$array;
}
if(is_array($array))
{
foreach($array as $key=>$value)
{
$array[$key] = object_array($value);
}
}
return $array;
}
?>
