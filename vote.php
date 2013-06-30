<?php 
define('MODULE_NAME','Vote');
$actions = array('detail','edit','update');
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
$fanwe->initialize();

require fimport('module/vote');

switch(ACTION_NAME)
{
	case 'detail':
		VoteModule::detail();
	break;
	case 'edit':
		VoteModule::edit();
	break;
	case 'update':
		VoteModule::update();
	break;
}
?>