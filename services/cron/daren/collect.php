<?php
require ROOT_PATH.'core/fanwe.php';
$fanwe = &FanweService::instance();
$fanwe->is_session = false;
$fanwe->is_user = false;
$fanwe->is_cron = false;
$fanwe->is_misc = false;
$fanwe->cache_list = array();
$fanwe->initialize();

$_FANWE['request'] = unserialize(REQUEST_ARGS);

@ignore_user_abort(true);
@ob_start();
@ob_end_flush(); 
@ob_implicit_flush(true);
echo str_repeat(' ',4096);

$sql = "SELECT * FROM ".FDB::table('item_collection')." WHERE status=0 ORDER BY gmt_create ASC";
$item = FDB::fetchFirst($sql);
$service = FS("Collect")->getPartnerServiceById($item["partner_id"]);
FS("Mogujie")->publishShares($item);

?>
