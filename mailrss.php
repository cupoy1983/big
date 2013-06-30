<?php 
define('MODULE_NAME','MailRss');

$actions = array('index','ajax_rss','ajax_remove_rss','show');
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

require fimport('module/mailrss');

switch(ACTION_NAME)
{
	case 'index':
		MailRssModule::index();
                break;
        case 'ajax_rss':
            MailRssModule::ajax_rss();
                break;
        case 'ajax_remove_rss':
            MailRssModule::ajax_remove_rss();
                break;
        case 'show':
            MailRssModule::show();
                break;
            
}
?>