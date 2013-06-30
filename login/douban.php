<?php
//人人登录接口
require_once FANWE_ROOT."sdks/douban/douban.func.php";
class douban
{
	private $config;
	public function __construct()
	{
		global $_FANWE;
		$this->config = $_FANWE['cache']['logins']['douban'];
	}
        /**
         * 获取缓存中的配置信息
         * @global type $_FANWE
         * @return string 
         */
	public function getInfo()
	{
		global $_FANWE;
		$data['name'] = $this->config['name'];
		$data['short_name'] = $this->config['short_name'];
		$data['is_syn'] = $this->config['is_syn'];
		$data['bind_img'] = SITE_URL.'login/douban/bind_douban.png';
		$data['icon_img'] = SITE_URL.'login/douban/icon_douban.png';
		$data['login_img'] = SITE_URL.'login/douban/login_douban.png';
		$data['login_url'] = SITE_URL."login.php?mod=douban";
		$data['bind_url'] = SITE_URL."login.php?bind=douban";
		$data['unbind_url'] = SITE_URL."login.php?unbind=douban";
		return $data;
	}

	public function loginJump()
	{
		global $_FANWE;
		if($_FANWE['uid'] > 0)
		{
			$this->bindJump();
			exit;
		}
		
		fSetCookie('callback_type','login');
		$this->jump();
	}

	public function bindJump()
	{
		global $_FANWE;
		if($_FANWE['uid'] == 0)
		{
			$this->loginJump();
			exit;
		}
		
		fSetCookie('callback_type','bind');
		$this->jump();
	}

	public function sessionJump()
	{
		global $_FANWE;
		FanweService::instance()->cache->loadCache('business');
		$this->config = $_FANWE['cache']['business']['douban'];
		fSetCookie('callback_type','session');
		$this->jump(1);
	}
	
	private function jump($type = 0)
	{
		if($type == 0)
			$url = FU('tgo',array('url'=>GetDoubanLoginUrl($this->config['app_key'])));
		fHeader("location:".$url);
	}

	public function unBind()
	{
		global $_FANWE;
		if($_FANWE['uid'] > 0)
		{
			FDB::delete('user_bind',"uid = ".$_FANWE['uid']." AND type = 'douban'");
		}
		fHeader("location: ".FU('settings/bind'));
	}
}
?>