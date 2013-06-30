<?php
// +----------------------------------------------------------------------
// | Fanwe 多语商城建站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 云淡风轻(97139915@qq.com)
// +----------------------------------------------------------------------


// 管理员
class SmtpAction extends CommonAction{

    public function checkSSL()
	{
		if(extension_loaded("openssl"))
		{
			echo 1;
		}
		else
		{
			echo 0;
		}
	}
     public function sendDemo(){
         vendor("common");
         global $_FANWE;

         $stmp = $_REQUEST;
         require_once fimport('mail.class','class');
         $mail = new Mail();
         $mail->setStmp($stmp);
         $name = '测试邮件';
         $email = trim($stmp['mail_address']);
         $subject = '测试邮件';
         $content = '测试邮件';
         if($mail-> send($name, $email, $subject, $content)){
             echo '发送成功';
         }else{
             echo '发送失败';
         }
         exit;
     }
}
?>