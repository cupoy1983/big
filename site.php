<?php
define('MODULE_NAME', 'Site');

$actions = array('tag','album');
$action = 'album';
if(isset($_REQUEST['action'])){
	$action = strtolower($_REQUEST['action']);
	if(! in_array($action, $actions)){
		$action = 'album';
	}
}

define('Action_NAME', $action);

require dirname(__FILE__).'/core/fanwe.php';
$fanwe = &FanweService::instance();
$fanwe->initialize();

require fimport('module/site');

switch(Action_NAME) {
	
	case 'tag' :
		SiteModule::tag();
		break;
	case 'album' :
		SiteModule::album();
		break;
}

?>