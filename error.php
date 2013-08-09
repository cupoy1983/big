<?php
define('MODULE_NAME', 'Error');

$actions = array('404');
$action = '404';
if(isset($_REQUEST['action'])){
	$action = strtolower($_REQUEST['action']);
	if(! in_array($action, $actions)){
		$action = '404';
	}
}
define('Action_NAME', $action);

require dirname(__FILE__).'/core/fanwe.php';
$fanwe = &FanweService::instance();
$fanwe->initialize();

require fimport('module/error');

switch(Action_NAME) {
	
	case '404' :
		ErrorModule::_404();
		break;
}

?>