<?php
$id = intval($_FANWE['request']['id']);
if($id == 0)
	exit;

extract(FS('Goods')->getCommentList($id,$_FANWE['page']));
include template("services/note/goods_comment");
display();
?>