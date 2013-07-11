<?php 
define('MODULE_NAME','Topic');
//detail：详情页面
//create：新建页面
//save：保存
//edit：编辑 页面
//update:更新
$actions = array('detail','create','save','edit','update');
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

require fimport('module/topic');

switch(ACTION_NAME)
{
	case 'create':
		TopicModule::create();
	break;
	case 'save':
		TopicModule::save();
	break;
	case 'edit':
		TopicModule::edit();
	break;
	case 'update':
		TopicModule::update();
	break;
	default:
		TopicModule::detail();
	break;
}
?>