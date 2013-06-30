<?php 
define('MODULE_NAME','Activity');
$actions = array('detail','manage','export','edit','update');
$action = 'detail';
if(isset($_REQUEST['action']))
{
	$action = strtolower($_REQUEST['action']);
	if(!in_array($action,$actions))
		$action = 'detail';
}


define('ACTION_NAME',$action);

require dirname(__FILE__).'/core/fanwe.php';
$fanwe = &FanweService::instance();
$fanwe->cache_list[] = 'activity_cate';
$fanwe->cache_list[] = 'citys';
$fanwe->initialize();

require fimport('module/activity');

switch(ACTION_NAME)
{
	case 'detail':
		ActivityModule::detail();
	break;
	case 'manage':
		ActivityModule::manage();
	break;
	case 'export':
		ActivityModule::export();
	break;
	case 'edit':
		ActivityModule::edit();
	break;
	case 'update':
		ActivityModule::update();
	break;
}
?>