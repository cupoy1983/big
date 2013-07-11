<?php
require fimport('service/share');
$result = array();
$id = intval($_FANWE['request']['pid']);

if($id == 0){
	exit;
}

FS('Topic')->deletePost($id);

$result['status'] = 1;
outputJson($result);

?>