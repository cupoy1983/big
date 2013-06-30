<?php
if(!isset($_FANWE['request']['url']) || empty($_FANWE['request']['url']) || !parseUrl($_FANWE['request']['url']))
	exit;

$result = array();
$result['error_code'] = 0;
$result['status'] = 0;

if($_FANWE['setting']['share_image_save_type'] == 1)
{
	$size = getImageLength($_FANWE['request']['url']);
	if($size == 0)
	{
		$result['status'] = -1;
		outputJson($result);
	}
	elseif($size > $_FANWE['setting']['max_upload'])
	{
		$result['status'] = -2;
		outputJson($result);
	}
}

$result['image_server'] = '';
if($_FANWE['setting']['share_image_save_type'] == 1)
{
	$img = getTempCache(md5($_FANWE['request']['url']));
	if($img === NULL)
	{
		if(!IS_UPYUN && FS("Image")->getIsServer())
		{
			$args = array();
			$args['pic_url'] = $_FANWE['request']['url'];
			$server = FS("Image")->formatServer($_FANWE['request']['image_server'],'DE');
			$server = FS("Image")->getImageUrlToken($args,$server,1);
			$body = FS("Image")->sendRequest($server,'savetemp',true);
			if(empty($body))
			{
				outputJson($result);
			}
			$img = unserialize($body);
			$result['image_server'] = $server['image_server'];
		}
		else
		{
			$img = copyFile($_FANWE['request']['url'],"temp",false);
			if($img === false)
			{
				if($image->error() == -105)
					$result['status'] = -2;
				outputJson($result);
			}
			$img['server_code'] = '';
		}
	}
	
	if($img)
	{
		setTempCache(md5($_FANWE['request']['url']),$img);
		$result['status'] = 1;
		$result['img'] = $img['url'];
		$info = array('path'=>$img['path'],'url'=>$img['url'],'type'=>'default','server_code'=>$img['server_code'],'image_width'=>$img['width'],'image_height'=>$img['height']);
		$result['info'] = base64_encode(json_encode($info));
	}
	else
	{
		outputJson($result);
	}
}
else
{
	$result['img'] = $_FANWE['request']['url'];
	$result['status'] = 1;
	$info = array('path'=>$_FANWE['request']['url'],'url'=>$_FANWE['request']['url'],'type'=>'default','server_code'=>'','image_width'=>(int)$_FANWE['request']['width'],'image_height'=>(int)$_FANWE['request']['height']);
	$result['info'] = base64_encode(json_encode($info));
}

$args = array('result'=>$result);
$result['html'] = tplFetch("services/share/pic_item",$args);
outputJson($result);
?>