<?php
/**
 * 该Service主要用于分熟采集
 * 通过采集URL构造对象， fetch获取采集内容
 * 采集结果结构参考 class/sharegoods/sharegoods.php interface_sharegoods接口规范
 *
 * 例：
 * $share = new SharegoodsService($url);
 * $share->fetch();
 *
 *
 * @author fzmatthew
 *
 */
class SharegoodsService{
	private $share_module;
	private $url;
	public $isGoods;
	
	//通过URL构造获取相应的采集解析模型
	public function __construct($url)
	{
		global $_FANWE;
		$this->isGoods = false;
		
		$class = $this->getModuleByUrl($url);
		if($class)
		{
			if(strpos($class,'yiqifa') !== FALSE)
			{
				$_FANWE['yiqifa_shop_id'] = str_replace("yiqifa","",$class);
				$class = 'yiqifa';
			}
			
			$file = FANWE_ROOT."core/class/sharegoods/".$class."_sharegoods.class.php";
			require_once FANWE_ROOT."core/class/sharegoods/sharegoods.php";
			require_once FANWE_ROOT."core/class/string.class.php";
			if(file_exists($file))
			{
				require_once $file;
				$class_name = $class."_sharegoods";
				if(class_exists($class_name))
				{
					$this->share_module = new $class_name;
					$this->isGoods = true;
				}
			}
		}
		$this->url = $url;
	}

	/**
	 * 返回结果为false时采集失败
	 */
	public function fetch()
	{
		if($this->share_module)
		{
			return $this->share_module->fetch($this->url);
		}
		else
			return false;
	}

	/**
	 * 获取该商品的标识，用于检测是否已经采集
	 */
	public function getKey()
	{
		if($this->share_module)
		{
			return $this->share_module->getKey($this->url);
		}
		else
			return '';
	}

	/**
	 * 检测是否已经采集过商品
	 */
	public function getExists($goods)
	{
		$key = $this->getKey();
		if(isset($goods[$key]))
			return true;
		else
			return false;
	}
	
	public function getModuleByUrl($url)
	{
		global $_FANWE;
		FanweService::instance()->cache->loadCache('business');
		
		$urls= parse_url($url);
		if(empty($urls['scheme']) || empty($urls['host']))
			return false;
		
		$full_domain = preg_quote($urls['host']).'$';
        $host = explode('.',$urls['host']);
        array_shift($host);
        $sub_domain = preg_quote(implode('.',$host)).'$';
		
		foreach($_FANWE['cache']['business'] as $key => $val)
		{
			$domains = explode(',',$val['domain']);
			foreach($domains as $domain_item)
			{
				if(preg_match("/".$sub_domain."/i",$domain_item))
					return $key;
						
				/*if(strpos($domain_item,'*.') !== FALSE)
				{
					if(preg_match("/".$sub_domain."/i",$domain_item))
						return $key;
				}
				else
				{
					if(preg_match("/".$sub_domain."/i",$domain_item))
						return $key;
				}*/
			}
		}
		return false;
	}
}
?>