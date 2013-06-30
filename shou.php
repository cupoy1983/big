<?php 
define('MODULE_NAME','Shou');
$actions = array('index','help');
$action = 'index';
if(isset($_REQUEST['action']))
{
	$action = strtolower($_REQUEST['action']);
	if(!in_array($action,$actions))
		$action = 'index';
}

define('ACTION_NAME',$action);

require dirname(__FILE__).'/core/fanwe.php';
$fanwe = &FanweService::instance();
$fanwe->initialize();

require fimport('module/shou');

switch(ACTION_NAME)
{
	case 'index':
		ShouModule::index();
	break;
	
	case 'help':
		ShouModule::help();
	break;
}
?>