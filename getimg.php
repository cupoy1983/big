<?php
define('FANWE_ROOT', str_replace('getimg.php', '', str_replace('\\', '/', __FILE__)));

if(!include(FANWE_ROOT.'./core/function/global.func.php'))
	exit;

@include FANWE_ROOT.'./public/config.global.php';

@include(FANWE_ROOT.'./public/data/caches/system/setting.cache.php');
define('IMAGE_CREATE_QUALITY',(int)$data['setting']['image_create_quality']);

//充许动态生成的图片规格
$config['image_sizes'] = array(
	'32x32',
	'64x64',
	'100x100',
	'160x160',
	'180x180',
	'200x999',
	'468x468',
	'960x150',
	'400x230',
	'220x220',
	'180x239',
);

$php_self = htmlspecialchars(getPhpSelf());
if($php_self === false)
	exit;

$site_path = substr($php_self, 0, strrpos($php_self, '/'));
$site_url = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$site_path.'/');

$file = $_REQUEST['img'];
if(empty($file))
	exit;

$gen = (int)$_REQUEST['gen'];
$arr = explode('_',$file);
$path = $arr[0];
$arr = explode('.',$arr[1]);
$size = $arr[0];
$size = explode('x',$size);
$width = (int)$size[0];
$height = (int)$size[1];
$ext = $arr[1];

if(array_search($width.'x'.$height,$config['image_sizes']) === FALSE)
	exit;

$path = FANWE_ROOT.$path.'.'.$ext;

if(file_exists(FANWE_ROOT.$file))
{
	@header("location: ".$site_url.$file,true);
	exit;
}

if(!file_exists($path))
	exit;

include_once fimport('class/image');
$image = new Image();
$image->max_size = 8192;
$img = $image->thumb($path,$width,$height,$gen);
if($img === false)
    exit;

@header("location: ".$site_url.$file,true);
?>