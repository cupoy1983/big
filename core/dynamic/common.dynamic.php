<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

/**  
 * dynamic.func.php
 *
 * 模板动态内容处理函数
 *
 * @package function
 * @author awfigq <awfigq@qq.com>
 */
 
/**  
 * 直接输出内容 
 * @param mixed $value 内容
 * @return string
 */ 
function echoValue($value)
{
	return $value;
}

/**  
 * 动态脚本 
 * @return string
 */ 
function getScript()
{
	return tplFetch('inc/script');
}

/**  
 * 动态脚本 
 * @return string
 */ 
function dynamicScript()
{
	global $_FANWE;
	return 'var USER_ID = '.$_FANWE['uid'].';
	var TAOBAO_IS_TDJ = '.(int)$_FANWE['cache']['business']['taobao']['is_tdj'].';
	var IS_OPEN_COMMISSION = '.(int)$_FANWE['setting']['is_open_commission'].';
	var USER_COMMISSION_RATE = '.(int)$_FANWE['user_group']['commission_rate'].';
	var USER_BUY_RATE = '.(int)$_FANWE['user_group']['goods_buy_score_rate'].';
    var GOODS_COMMISSION_SCORE_RATE = '.(int)$_FANWE['user_group']['goods_commission_score_rate'].';';
}

/**  
 * HEAD CLASS 
 * @return string
 */ 
function headClass()
{
	global $_FANWE;
	if($_FANWE['uid'] == 0)
            return " HEADER_TOP_LOGIN_SCROLL";
        else
            return "";
}

/**  
 * 获取会员相关信息 
 * @return string
 */ 
function getUserInfo()
{
	return tplFetch('inc/common/user_info');
}

/**  
 * 获取hash码
 * @return string
 */ 
function getRHash()
{
	return FORM_HASH;
}

/**  
 * 获取随机码
 * @return string
 */ 
function getRandom()
{
	return random(8);
}

/**  
 * 广告位显示
 * @param array $args 广告位信息
 * @return string
 */ 
function advLayout($args)
{
	list($id,$count,$target) = explode(',',$args);
	unset($args);
	$ap = getAdvPosition($id,$count,$target);
	if(empty($ap))
		return '';
	
	$style = $ap['style'];
	unset($ap['style']);
	
	$cache_key = 'adv_position/'.$ap['id'];
	
	if($ap['is_flash'] == 1 && !empty($ap['flash_style']))
	{
		$args['adv_path'] = "./public/adflash/".$ap['flash_style'].".swf";
		$args['adv_pics']="";
		$args['adv_texts']="";
		$args['adv_links']="";
	
		$jg = '';
		foreach($ap['adv_list'] as $adv)
		{
			$args['adv_pics'].=$jg.$adv['code'];
			$args['adv_texts'].=$jg.$adv['desc'];
			$args['adv_links'].=$jg.$adv['url'];
			$jg = "|";
		}
	
		unset($ap['adv_list']);
		$args['adv_position'] = $ap;
	}
	else
	{
		$args['adv_list'] = $ap['adv_list'];
	}
	
	unset($ap);
	return tplString($style,$cache_key,$args);
}


function advLayoutName($args)
{
	list($name,$tmpl,$target) = explode(',',$args);
	
	unset($args);
	$sql = "SELECT `id`,`rec_id`,`item_limit`,`target_id` FROM ".FDB::table("adv_layout")." where `layout_id`='$name' and `tmpl` ='".TMPL."' ";
	$rs = FDB::fetchFirst($sql);
	if(!$rs)
		return "";
	if($target)
		$rs['target_id'] = $target;
	$ap = getAdvPosition($rs['rec_id'],$rs['item_limit'],$rs['target_id']);
	if(empty($ap))
		return '';
	if($tmpl){
		$tmpl_path = FANWE_ROOT.'./tpl/'.TMPL."/".$tmpl.".htm";
		$style = @file_get_contents($tmpl_path);
	}
	else
		$style = $ap['style'];
	unset($ap['style']);
	
	$cache_key = 'adv_position/'.$ap['id'];
	
	if($ap['is_flash'] == 1 && !empty($ap['flash_style']))
	{
		$args['adv_path'] = "./public/adflash/".$ap['flash_style'].".swf";
		$args['adv_pics']="";
		$args['adv_texts']="";
		$args['adv_links']="";
	
		$jg = '';
		foreach($ap['adv_list'] as $adv)
		{
			$args['adv_pics'].=$jg.$adv['code'];
			$args['adv_texts'].=$jg.$adv['desc'];
			$args['adv_links'].=$jg.$adv['url'];
			$jg = "|";
		}
	
		unset($ap['adv_list']);
		$args['adv_position'] = $ap;
	}
	else
	{
		$args['adv_list'] = $ap['adv_list'];
	}
	
	unset($ap);
	return tplString($style,$cache_key,$args);
}

function getManageDynamic($page)
{
	$args['page'] = $page;
	return tplFetch('inc/manage',$args);
}
?>