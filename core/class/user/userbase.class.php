<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

class UserBase
{
	public function getConfig($type)
	{
		static $configs = NULL;
		if($configs === NULL)
		{
			global $_FANWE;
			if(!isset($_FANWE['cache']['logins']))
				FanweService::instance()->cache->loadCache('logins');
			$configs = $_FANWE['cache']['logins'];
		}
		return $configs[$type];
	}
	
	public function getUserByTypeKeyId($type,$key_id)
	{
		$sql = 'SELECT u.uid,u.password,u.status FROM '.FDB::table('user_bind').' as ub 
			INNER JOIN '.FDB::table('user').' as u ON u.uid = ub.uid 
			WHERE ub.type = \''.$type.'\' AND ub.keyid = \''.$key_id.'\'';

		return FDB::fetchFirst($sql);
	}
	
	/*
	 * 2013.3.15
	* 第三方用户登录不需要绑定
	*/
	public function jumpUserBindReg($data,$user_name)
	{
		do{
                    $max_count = FDB::resultFirst('SELECT COUNT(*) FROM '.FDB::table("user")." WHERE user_name = '".$user_name."'");
                    if($max_count > 0)
                            $user_name = $user_name.'_'.random(3);
            }while($max_count > 0);

		if($data['type']=='qq'){	//2013.3无绑定路口
			$url = FU('user/bindlogin');
		}else{
			$url = FU('user/bind');
		}
		$data['user_name'] = $user_name;
		$data = serialize($data);
		fSetCookie('bind_user_info',authcode($data,'ENCODE'));
		fHeader("location:".$url);
	}
}
?>