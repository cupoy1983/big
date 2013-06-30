<?php
$result['timestamp']= '';
$result['sign']='';
if(isset($_FANWE['cache']['business']['taobao']) && $_FANWE['cache']['business']['taobao']['is_tdj'] == 0)
{
        $app_key = $_FANWE['cache']['business']['taobao']['app_key'];
        $secret = $_FANWE['cache']['business']['taobao']['app_secret'];
        $timestamp = time()."000";
        $message = $secret.'app_key'.$app_key.'timestamp'.$timestamp.$secret;
        $mysign=strtoupper(hash_hmac("md5",$message,$secret));
        $result['timestamp']= $timestamp;
        $result['sign']=$mysign;
}
outputJson($result);
?>
