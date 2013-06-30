<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: awfigq <awfigq@qq.com>
// +----------------------------------------------------------------------
/**
 +------------------------------------------------------------------------------
 * SEO设置
 +------------------------------------------------------------------------------
 */
class SeoAction extends CommonAction
{
	public function index()
	{
		$site_url = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/';
		$this->assign("site_url",$site_url);
		
		$seos = array();
		$list = D("Seo")->order('id asc')->select();
		foreach($list as $item)
		{
			$item['rewrite'] = unserialize($item['rewrite']);
			$item['rewrite_str'] = '';
			$item['rewrite_module'] = $item['rewrite']['module'];
			$item['rewrite_type'] = $item['rewrite']['is_action_type'];
			$item['rewrite_action'] = $item['rewrite']['action'];
			$item['rewrite_args'] = '';
			if(!empty($item['rewrite']['args']))
				$item['rewrite_args'].="/".$item['rewrite']['args'];
			
			if(!empty($item['rewrite']['action']))
				$item['rewrite_str'].="/".$item['rewrite']['action'];

			$seos[$item['type']]['rewrite'] = $item['rewrite']['module'];
			$seos[$item['type']]['list'][] = $item;
		}
		
		$this->assign("seos",$seos);
		$this->display();
	}
	
	public function update()
	{
		vendor("common");
		$modules = array();
		$list = D("Seo")->order('id asc')->select();
		foreach($list as $item)
		{
			$id = $item['id'];
			$item['rewrite'] = unserialize($item['rewrite']);
			$item['title'] = $_POST['seo'][$item['type']][$item['key']]['title'];
			$item['keywords'] = $_POST['seo'][$item['type']][$item['key']]['keywords'];
			$item['description'] = $_POST['seo'][$item['type']][$item['key']]['description'];
			$item['rewrite']['module'] = $_POST['seo'][$item['type']]['module'];
			if(isset($_POST['seo'][$item['type']][$item['key']]['action']))
				$item['rewrite']['action'] = $_POST['seo'][$item['type']][$item['key']]['action'];
			
			$module = $_POST['seo'][$item['type']]['module'];
			$action = str_replace($item['type'].'_','',$item['key']);
			$modules[$item['type']]['module'] = $module;
			$modules[$item['type']][$action]['action'] = $item['rewrite']['action'];
			$modules[$item['type']][$action]['seo']['title'] = array();
			$temps = explode("\n",$item['title']);
			foreach($temps as $temp)
			{
				$temp = trim($temp);
				if(!empty($temp))
				{
					preg_match_all("/\{(.+?)\}/",$temp,$match);
					$modules[$item['type']][$action]['seo']['title'][] = array('args' => $match[1],'content' => $temp);
				}
			}
			
			$modules[$item['type']][$action]['seo']['keywords'] = array();
			$temps = explode("\n",$item['keywords']);
			foreach($temps as $temp)
			{
				$temp = trim($temp);
				if(!empty($temp))
				{
					preg_match_all("/\{(.+?)\}/",$temp,$match);
					$modules[$item['type']][$action]['seo']['keywords'][] = array('args' => $match[1],'content' => $temp);
				}
			}
			
			$modules[$item['type']][$action]['seo']['description'] = array();
			$temps = explode("\n",$item['description']);
			foreach($temps as $temp)
			{
				$temp = trim($temp);
				if(!empty($temp))
				{
					preg_match_all("/\{(.+?)\}/",$temp,$match);
					$modules[$item['type']][$action]['seo']['description'][] = array('args' => $match[1],'content' => $temp);
				}
			}
			
			$item['rewrite'] = serialize($item['rewrite']);
			unset($item['id']);
			D("Seo")->where('id = '.$id)->save($item);
		}
		
		$search = array();
		$replace = array();
		foreach($modules as $mkey => $mitem)
		{
			foreach($mitem as $akey => $aitem)
			{
				if($akey == 'module')
				{
					$search[] = '{'.strtoupper($mkey).'}';
					$replace[] = $aitem;
				}
				else
				{
					$search[] = '{'.strtoupper($mkey.'_'.$akey).'}';
					$replace[] = ($aitem['action'] === '') ? $aitem['action'] : '/'.$aitem['action'];
				}
			}
		}
		$rules = @file_get_contents(FANWE_ROOT."./public/seos/rules/httpd.ini");
		$rules =  str_replace($search,$replace,$rules);
		@file_put_contents(FANWE_ROOT."./public/seos/result/httpd.ini",$rules);
		@file_put_contents(FANWE_ROOT."./rewrite/httpd.ini",$rules);
		
		$rules = @file_get_contents(FANWE_ROOT."./public/seos/rules/web.config");
		$rules =  str_replace($search,$replace,$rules);
		@file_put_contents(FANWE_ROOT."./public/seos/result/web.config",$rules);
		@file_put_contents(FANWE_ROOT."web.config",$rules);
		
		$rules = @file_get_contents(FANWE_ROOT."./public/seos/rules/.htaccess");
		$rules =  str_replace($search,$replace,$rules);
		@file_put_contents(FANWE_ROOT."./public/seos/result/.htaccess",$rules);
		@file_put_contents(FANWE_ROOT.".htaccess",$rules);
		
		$rules = @file_get_contents(FANWE_ROOT."./public/seos/rules/nginx.conf");
		$rules =  str_replace($search,$replace,$rules);
		@file_put_contents(FANWE_ROOT."./public/seos/result/nginx.conf",$rules);
		@file_put_contents(FANWE_ROOT."nginx.conf",$rules);

		FanweService::instance()->cache->saveCache('seos', $modules);
		
		$this->saveLog(1);
		$this->success(L('EDIT_SUCCESS'));
	}
	
	public function downrule()
	{
		$type = $_REQUEST['type'];
		switch($type)
		{
			case "iis6":
				$file = 'httpd.ini';
			break;
			
			case "htaccess":
				$file = '.htaccess';
			break;
			
			case "iis7":
				$file = 'web.config';
			break;
			
			case "nginx":
				$file = 'nginx.conf';
			break;
			
			default:
				exit;
			break;
		}
		
		$content = @file_get_contents(FANWE_ROOT."./public/seos/result/".$file);
		$time = gmtTime();
		header('Last-Modified: '.gmdate('D, d M Y H:i:s',$time).' GMT');
		header('Cache-control: no-cache');
		header('Content-Encoding: none');
		header('Content-Disposition: attachment; filename="'.$file.'"');
		header('Content-type: txt');
		header('Content-Length: '.strlen($content));
		echo $content;
		exit;
	}
}
?>