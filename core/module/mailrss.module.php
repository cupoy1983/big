<?php

/*
 * 邮件订阅系统
 * jobin.lin
 */
class MailRssModule
{
    function index(){
        global $_FANWE;

        if($_FANWE['uid'] ){
            $type = 1;
        }
        //订阅列表
        $rss_cates['list'] = FS('MailRss')->getRssCate($type);
        if($type == 1)
            $rss_cates['list_1'] = FS('MailRss')->getRssCate(2);
        
        //订阅用户列表
        $rss_user_list = FS('MailRss')->getRssUser(array(0,10));

        include template('page/mailrss/mailrss_index');
        display();
        
    }
        
    function show(){
        global $_FANWE;
        $id = (int)$_FANWE['request']['id'];
        if(empty ($id))
            return false;

        $rss_cate = FS('MailRss')->getRssCateById($id);

        //订阅用户列表
        $rss_user_list = FS('MailRss')->getRssUser(array(0,10));
        include template('page/mailrss/mailrss_show');
        display();
        
    }
    
    function ajax_rss(){
        global $_FANWE;
        if(empty($_FANWE['uid']))
            return FALSE;
        
        $data['uid'] = $_FANWE['uid'];
        $data['cate_id'] = $_FANWE['request']['cate_id'];
        $data['create_time'] = TIME_UTC;
        $data['status'] = 1;
        $data['rss_sn'] = md5('mail_rss_'.$data['uid'].'_'.$data['cate_id'].'_'.TIME_UTC);
        
        if(FS('MailRss')->saveRss($data))
                $result['status'] = 1;
            outputJson($result);
        
    }

    
    function ajax_remove_rss(){
        global $_FANWE;
        if(empty($_FANWE['uid']))
            return FALSE;
        if(FS('MailRss')->removeRss($_FANWE['request']['cate_id'],$_FANWE['uid']))
                 $result['status'] = 1;
             outputJson($result);
    }
}
?>
