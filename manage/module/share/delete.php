<?php
require fimport('service/share');
$result = array();
$id = intval($_FANWE['request']['id']);

if($id == 0)
	exit;

$share = FS('Share')->getShareById($id);
if(empty($share))
{
	$result['status'] = 1;
	outputJson($result);
}

if(checkAuthority('share','delete'))
	$authoritys = 3;
else
	$authoritys = FS('Share')->getIsEditShare($share);

if($authoritys == 0)
{
	$result['status'] = 0;
	outputJson($result);
}

$type = intval($_FANWE['request']['type']);
if($type == 0)
{
	switch($share['type'])
	{
		case 'bar':
			FS('Topic')->deleteTopic($share['rec_id']);
		break;	
		case 'album':
			FS('Album')->deleteAlbum($share['rec_id']);
		break;	
		case 'album_item':
			FS('Album')->deleteAlbumItem($id);
		break;
		case 'activity':
			FS('Activity')->delete($share['rec_id']);
		break;	
		case 'activity_post':
			FS('Activity')->deletePost($id);
		break;
		case 'vote':
			FS('Vote')->delete($share['rec_id']);
		break;	
		case 'vote_post':
			FS('Vote')->deletePost($id);
		break;	
		default:
			FS('Share')->deleteShare($id);
		break;
	}
	$result['status'] = 1;
}
elseif($type == 1)
{
	if($authoritys < 2)
	{
		$result['status'] = 0;
		outputJson($result);
	}
	
	FDB::query('UPDATE '.FDB::table('share').' SET 
		type = \'default\',
		rec_id = 0,
		parent_id = 0 
		WHERE share_id = '.$id);
	if($share['type'] == 'vote_post')
		FS('Vote')->deletePost($id);
	FS('Share')->deleteShareCache($id);
	$result['status'] = 1;
}

outputJson($result);
?>