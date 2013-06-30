<?php
require ROOT_PATH.'core/fanwe.php';
$fanwe = &FanweService::instance();
$fanwe->is_session = false;
$fanwe->is_user = false;
$fanwe->is_cron = false;
$fanwe->is_misc = false;
$fanwe->cache_list = array('goods_category');
$fanwe->initialize();

$_FANWE['request'] = unserialize(REQUEST_ARGS);
$args = $_FANWE['request']['args'];
foreach($args as $arg)
{
	$share_ids = array();
	$sql = 'SELECT DISTINCT(sgi.share_id) FROM '.FDB::table('share_tags').' AS st ';
	$sql_type = 'st';
	if($arg['cid'] > 0)
	{
		$sql_type = 'sc';
		$sql .= 'INNER JOIN '.FDB::table('share_category').' AS sc 
			ON sc.share_id = st.share_id AND sc.cate_id = '.$arg['cid'].' ';
	}
	$sql .= 'INNER JOIN '.FDB::table('share_goods_index').' AS sgi 
		ON sgi.share_id = '.$sql_type.'.share_id ';
	
	$sql .= " WHERE st.tag_name = '".addslashes($arg['tag'])."' ORDER BY sgi.share_id DESC LIMIT 0,32";
	$res = FDB::query($sql);
	while($data = FDB::fetch($res))
	{
		$share_ids[] = (int)$data['share_id'];
	}
	$key = md5($arg['tag'].','.$arg['cid']);
	$key = 'share/related/'.substr($key,0,2).'/'.substr($key,2,2).'/'.$key;
	setCache($key,$share_ids,SHARE_CACHE_TIME);
}
?>
