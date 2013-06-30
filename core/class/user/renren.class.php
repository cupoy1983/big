<?php

// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

require_once 'userbase.class.php';
require_once FANWE_ROOT . "sdks/renren/renren.func.php";
require_once FANWE_ROOT . "sdks/renren/RenrenRestApiService.class.php";

class RenrenUser extends UserBase {

    public $config;
    private $type = 'renren';

    public function RenrenUser() {
        $this->config = $this->getConfig($this->type);
    }

    public function loginHandler($user) {
        global $_FANWE;

        $bind_user = $this->getUserByTypeKeyId($this->type, $user['id']);
        if ($bind_user) {
            if ($bind_user['status'] == 0)
                showError('登陆失败', '该帐户已被管理员锁定', FU('index'));

            $_FANWE['uid'] = $bind_user['uid'];
            $this->updateBindInfo($user);
            FS('User')->setSession($bind_user, 1209600);
        }
        else {
            $data = array();
            $data['type'] = $this->type;
            $data['user'] = $user;
            $this->jumpUserBindReg($data, $user['name']);
        }
    }

    public function bindHandler($user) {
        global $_FANWE;
        if ($_FANWE['uid'] == 0)
            exit;

        $bind_user = $this->getUserByTypeKeyId($this->type, $user['id']);
        if ($bind_user && $bind_user['uid'] != $_FANWE['uid']) {
            $data = array();
            $data['short_name'] = $this->config['short_name'];
            $data['keyid'] = $user['id'];
            $data['type'] = $this->type;
            $data['user'] = $user;
            fSetCookie('sync_bind_exists', authcode(serialize($data), 'ENCODE'));
        } else {
            $this->bindUser($user);
        }
    }

    public function bindByData($data) {
        $this->bindUser($data['user']);
    }

    public function updateBindInfo($user) {
        global $_FANWE;
        $info = array();
        $info['access_token'] = $user['access_token'];
        unset($user['access_token']);
        $info['user'] = $user;

        $data = array();
        $data['info'] = addslashes(serialize($info));
        FDB::update('user_bind', $data, "uid = " . $_FANWE['uid'] . " AND type = '" . $this->type . "'");
    }

    public function bindUser($user) {
        if ($user) {
            global $_FANWE;
            $data = array();
            $data['uid'] = $_FANWE['uid'];
            $data['type'] = $this->type;
            $data['keyid'] = $user['id'];
            unset($user['id']);
            $data['refresh_time'] = 0;

            $info = array();
            $info['access_token'] = $user['access_token'];
            unset($user['access_token']);
            $info['user'] = $user;
            $data['info'] = addslashes(serialize($info));

            $sync = array();
            $sync['weibo'] = 1;
            $sync['topic'] = 1;
            $sync['medal'] = 1;
            $data['sync'] = serialize($sync);
            if (!empty($user['avatar'][3]['url']) && FS('User')->getAvatar($_FANWE['uid']) == 0) {
                $img = copyFile($user['avatar'][3]['url'], "temp", false);
                if ($img !== false)
                    FS('User')->saveAvatar($_FANWE['uid'], $img['path']);
            }
            FDB::insert('user_bind', $data, false, true);
            //绑定后推送网站信息
            /*
            if((int)$_FANWE['setting']['bind_push_weibo'] == 1)
            {
                    $weibo = array();
                    $weibo['content'] = sprintf(lang('user','bind_weibo_message'),$_FANWE['setting']['site_name'],$_FANWE['setting']['site_description'],$_FANWE['setting']['site_name']);
                    $weibo['img'] = "";
                    $weibo['ip'] = $_FANWE['client_ip'];
                    $weibo['url'] = FU('u/index',array('uid'=>$_FANWE['uid']),true);
                    $weibo['content'] = cutStr($weibo['content'],277 - strlen($weibo['url']));
                    $this->sentShare($_FANWE['uid'],$weibo);
            }
            */
        }
    }

    /**
     * 2013.3.15 update3.1 发布一条新鲜事
     * @param type $uid
     * @param type $data
     * @return type 
     */
    function sentShare($uid, $data) {
        $rrObj = new RenrenRestApiService();
        $rrObj->Init($this->config['app_key'], $this->config['app_secret']);

        $bind = FS("User")->getUserBindByType($uid, 'renren');

        //无标题则截取内容
        $title = $data['title'];
        if (empty($title))
            $title = cutStr($data['content'], 30);
        if (!empty($data['img_url']))
            $img_url = $data['img_url'];

        //$params = array('name'=>"这是name",'description'=>"这是描述",'url'=>"这是url",'image'=>"http://wiki.dev.renren.com/mediawiki/images/a/a7/Wiki2.png",'action_name'=>"这是action_name",'action_link'=>"http://www.renren.com",'message'=>"这是message",'access_token'=>$accesstoken);//使用access_token调api的情况

        $param_share['name'] = $title;
        $param_share['description'] = cutStr($data['content'], 200, '');
        $param_share['url'] = $data['url'];
        $param_share['message'] = cutStr($data['content'], 200, '');
        $param_share['image'] = $img_url;
        $param_share['access_token'] = $bind['access_token'];
        $res = $rrObj->rr_post_curl('feed.publishFeed', $param_share); //curl函数发送请求
        $arr = json_decode($res, true);
        if ($arr['post_id'] > 0)
            return true;
        else
            return false;
    }

}

?>