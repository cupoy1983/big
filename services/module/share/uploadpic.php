<?php
if(!isset($_FILES['image']) || empty($_FILES['image']))
	exit;

$result = array();
$pic = $_FILES['image'];
include_once fimport('class/image');
$image = new Image();
if(intval($_FANWE['setting']['max_upload']) > 0)
	$image->max_size = intval($_FANWE['setting']['max_upload']);
$image->init($pic);

if($image->save())
{
	//$type = $_FANWE['request']['photo_type'];
	//if(empty($type) || !in_array($type,array('default', 'dapei', 'look')))
		//$type = 'default';
	
	$img_info  = $image->getImageInfo($image->file['local_target']);
	$result['img'] = $image->file['target'];
	$result['status'] = 1;
	$info = array('path'=>$image->file['local_target'],'url'=>$image->file['target'],'type'=>$_FANWE['request']['photo_type'],'server_code'=>'','image_width'=>$img_info[0],'image_height'=>$img_info[1]);
	$result['info'] = base64_encode(json_encode($info));
	$args = array('result'=>$result);
	$result['html'] = tplFetch("services/share/pic_item",$args);
}
else
{
	$result['status'] = 0;
	if($image->error() == -105)
		$result['status'] = -2;
}

$json = getJson($result);
echo "<textarea>$json</textarea>";
?>