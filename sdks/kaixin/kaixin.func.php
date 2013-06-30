<?php
/*
 * kaixin
 */
function getKaixinLoginUrl($appkey)
{
	global $_FANWE;
	$state = md5(uniqid(rand(),TRUE));//CSRF protection
	$url = "http://api.kaixin001.com/oauth2/authorize?response_type=code&client_id=".
             $appkey."&response_type=code&redirect_uri=".urlencode($_FANWE['site_url']."callback/kaixin.php")."&display=popup&state=".$state;
	fSetCookie('kaixin_state',$state);
	return $url;
}

function getKaixinAccessToken($app_key,$app_secret,$code)
{
    global $_FANWE;
    $kaixin_state = $_FANWE['cookie']['kaixin_state'];
    if($_REQUEST['state'] == $kaixin_state){
        $url = 'https://api.kaixin001.com/oauth2/access_token?grant_type=authorization_code&code='.$code.'&client_id='.$app_key.'&client_secret='.$app_secret.'&redirect_uri='.urlencode($_FANWE['site_url']."callback/kaixin.php");
        $response = file_get_contents($url);
        $response = (array)json_decode($response);
        return $response['access_token'];
    }
    else 
    {
        echo("The state does not match. You may be a victim of CSRF.");
    }
    
}
function getKaixinUserInfo($access_token)
{
	$url= "https://api.kaixin001.com/users/me.json?access_token=".$access_token;
	$response = file_get_contents($url);
        $response = (array)json_decode($response);
        $user = array();
        $user['id'] =   $response['uid'];
        $user['name'] = $response['name'];
        return $user;
}
?>
