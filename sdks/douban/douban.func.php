<?php
/*
 * Douban
 */
function GetDoubanLoginUrl($appkey)
{
	global $_FANWE;
	$state = md5(uniqid(rand(),TRUE));//CSRF protection
	$url = "https://www.douban.com/service/auth2/auth?client_id=".
             $appkey."&response_type=code&redirect_uri=".urlencode($_FANWE['site_url']."callback/douban.php")."&state=".$state;
	fSetCookie('douban_state',$state);
	return $url;
}

function getDoubanAccessToken($app_key,$app_secret,$code)
{
    global $_FANWE;
    $douban_state = $_FANWE['cookie']['douban_state'];
    if($_REQUEST['state'] == $douban_state){

        $url = 'https://www.douban.com/service/auth2/token';
        $postfield = 'client_id='.$app_key.'&client_secret='.$app_secret.'&redirect_uri='.urlencode($_FANWE['site_url']."callback/douban.php").'&grant_type=authorization_code&code='.$code;

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $postfield);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		$response = curl_exec($ch);
		if ($response === FALSE) {
			echo "cURL Error: " . curl_error($ch);
		}
		curl_close($ch);

        $params = json_decode($response);
        $params = (array)$params;
        return $params['access_token'];
    }
    else 
    {
        echo("The state does not match. You may be a victim of CSRF.");
    }
    
}
function getDoubanUserInfo($access_token)
{
	$url= "https://api.douban.com/v2/user/~me";
	$ch = curl_init();
	$headers[] = "Authorization: Bearer ".$access_token;

	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE );
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

        $content = curl_exec($ch);
        if ($content === FALSE) {
                echo "cURL Error: " . curl_error($ch);
        }
        curl_close($ch);
        $data = json_decode($content,true);

        $user = array();
        $user['name'] = $data['name'];
        $user['id'] =  $data['uid'];
        return $user;
}
?>
