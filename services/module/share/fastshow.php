<?php
$type = $_FANWE['request']['type'];
switch($type)
{
	case "goods":
		$info = $goods = $_FANWE['request']['goods'];
		if(empty($goods))
			exit;

		$goods = json_decode(base64_decode($goods),true);
		if(!$goods)
			exit;
		$result['status'] = 1;
		$result['info'] = $info;
		$result['type'] = 'g';
		$result['img'] = $goods['item']['pic_url'];
		$result['gid'] = $goods['item']['gid'];
		$result['key'] = $goods['item']['key'];
		if((int)$_FANWE['setting']['share_is_tag'] == 1)
			$result['tags'] = implode(' ',FS('Words')->segment($goods['item']['name'],$_FANWE['setting']['share_tag_count']));
		
		$args = array('type'=>'goods','goods'=>$goods,'result'=>$result);
		$result['image_server'] = $goods['image_server'];
	break;
	
	case "photo":
		$info = $photo = $_FANWE['request']['photo'];
		if(empty($photo))
			exit;
		
		$photo = json_decode(base64_decode($photo),true);
		if(!$photo)
			exit;
		
		$result['img'] = $photo['url'];
		$result['status'] = 1;
		$result['info'] = $info;
		$args = array('type'=>'photo','result'=>$result);
	break;
	
	default:
		exit;
	break;
}
$rel = $_FANWE['request']['rel'];
if(empty($rel)){
     $result['html'] = tplFetch("services/share/fast_show",$args);
}
else
{
	$args['rel'] = $rel;
	$result['html'] = tplFetch("services/share/fast_show_rel",$args);
}

outputJson($result);
?>