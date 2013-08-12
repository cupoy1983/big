<?php
if($_FANWE['uid'] == 0)
	exit;

$img = FS('Image')->save('album','temp',true,array('album'=>array(930,200,1)),true);

outputJson(array('src'=>$img['thumb']['album']['url']));
?>