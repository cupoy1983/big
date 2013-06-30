<?php

/*
 * 邮件内容管理
 * and open the template in the editor.
 */
class MailRssContentAction extends CommonAction
{
        public function index(){
		$model = D("MailRssContent");
		if (! empty ( $model ))
		{
			
			$count = $model->count('id');
			$sql = 'SELECT rcont.*,rcate.cate_name AS cate_name 
					FROM '.C("DB_PREFIX").'mail_rss_content as rcont 
					LEFT JOIN '.C("DB_PREFIX").'mail_rss_category as rcate ON rcate.cate_id = rcont.cate_id ';
			
			$this->_sqlList($model,$sql,$count,$map);
		}
		$this->display ();
		return;
        }
        
         public function insert()
	{
		$_POST['is_html'] = isset($_POST['is_html']) ? intval($_POST['is_html']) : 0;
		
		$model = D("MailRssContent");
		if(false === $data = $model->create())
		{
			$this->error($model->getError());
		}
		
		//保存当前数据对象
		$list=$model->add($data);
		if ($list !== false)
		{
			
			$this->saveLog(1,$list);
			$this->success (L('ADD_SUCCESS'));

		}
		else
		{
			$this->saveLog(0,$list);
			$this->error (L('ADD_ERROR'));
		}
	}
        public function add(){
            $cate_list = D("MailRssCategory")->where('status = 1')->field('cate_id,parent_id,cate_name')->order('sort ASC,cate_id ASC')->select();
		$cate_list = D("MailRssCategory")->toFormatTree($cate_list,'cate_name','cate_id','parent_id');
		$this->assign("cate_list",$cate_list);
                $this->display();
        }
        public function edit() {
            $id = $_REQUEST['id'];
            $vo = D('MailRssContent')->getById($id);
            $this->assign('vo',$vo);
            
            $cate_list = D("MailRssCategory")->where('status = 1')->field('cate_id,parent_id,cate_name')->order('sort ASC,cate_id ASC')->select();
            $cate_list = D("MailRssCategory")->toFormatTree($cate_list,'cate_name','cate_id','parent_id');
            $this->assign("cate_list",$cate_list);

            $this->display();
        }
        
        public function update(){
            $_POST['is_html'] = isset($_POST['is_html'])? intval($_POST['is_html']) : 0;
            $model = D('MailRssContent');
            if(false === $data = $model->create() ){
                $this->error($model->getError());
            }
            
            //保存当前数据对象
		$list=$model->save($data);
		if ($list !== false)
		{
			
			$this->saveLog(1,$list);
                        $this->assign('jumpUrl', Cookie::get ( '_currentUrl_' ) );
			$this->success (L('ADD_SUCCESS'));

		}
		else
		{
			$this->saveLog(0,$list);
			$this->error (L('ADD_ERROR'));
		}
        }
        
        public function sendMail(){
            $id = $_REQUEST['id'];
            vendor("common");
            global $_FANWE;

            require_once fimport('mail.class','class');
            //获取发布内容详细信息
            $mail_rss_content = D('mail_rss_content')->where('id = '.$id)->find();
            $mail_rss_category = D('mail_rss_category')->where('cate_id = '.$mail_rss_content['cate_id'])->find();
            //判断分类的状态
            if($mail_rss_category['status']===0){
                 $this->error (L('ADD_ERROR'));
            }
            if(!$mail_rss_content['is_html']){
                $sids_arr = explode(' ', $mail_rss_content['sids']);
                foreach ($sids_arr as $s_v){
                    $s_v = trim($s_v);
                    
                    if(intval($s_v)){
                        $sid_tmp[] = $s_v;
                    }
                }
                //分享ID编号
                $sids_in_str = implode(',', $sid_tmp);
                $sql = 'SELECT share_id,uid,content,collect_count,comment_count,create_time,cache_data 
			FROM '.FDB::table('share').' WHERE share_id IN ('.$sids_in_str.')';

                $share_list = D('share')->query($sql);
                foreach ($share_list as  $share_data) {
                    $share_list[$share_data['share_id']] = $share_data;
                }
                //获取分享详细数据列表
                $share_list = FS('Share')->getShareDetailList($share_list,false,false,false,true,2);
                $data['share_list'] = $share_list;
                $data['logo'] = $_FANWE['site_url'].$_FANWE['setting']['site_logo'];
                $args = array(
                        'data'=>&$data,
                        'site_name'=>$_FANWE['setting']['site_name'],
                        'site_url'=>$_FANWE['site_url']
                );
                $mail_html = tplFetch("inc/common/mail_rss_tpl",$args);
                
                
            }else{
                $mail_html = $mail_rss_content['html_content'];
            }
            $ru_sql = 'SELECT ru.*,u.email FROM '.  FDB::table('mail_rss_user').' ru left join '.FDB::table('user').' u on ru.uid = u.uid WHERE ru.cate_id='.$mail_rss_content['cate_id'].' and ru.status = 1';
            $send_users = D('mail_rss_user')->query($ru_sql);

            $mail = new Mail();
            $mail_info['type'] = 1; // 设置邮件格式为 HTML
            $mail_info['site_name'] = $_FANWE['setting']['site_name'];
            $mail_info['subject'] = '感谢订阅'.$_FANWE['setting']['site_name']; // 标题	
            //要发送的用户总数
            $user_count = count($send_users);
            //发送失败人数
            $fail_count = 0;
            foreach ($send_users as $u_k=>$u_v){
                $send_uid = $u_v['uid'];
                $send_cate_id = $mail_rss_content['cate_id'];
                $send_content_id = $mail_rss_content['id'];

                $cancel_url = '<div class="footer">如果您不想接续接收'.$_FANWE['setting']['site_name'].'邮件，您可以随时<a href="'.$_FANWE['site_url'].'user.php?action=mailRssCancel&rss_sn='.$u_v['rss_sn'].'">申请退订</a></div>';
                $mail_info['body'] =  $mail_html.$cancel_url; // 内容
                $mail_info['email'] = $u_v['email'];

                //邮件服务器循环发送
                $stmp_arr = FS('smtp')->loopSendMail($mail);
                if(!$mail-> send($mail_info['site_name'], $mail_info['email'] , $mail_info['subject'], $mail_info['body'],$mail_info['type']))
                {
                        D('mail_rss_send_log')->data(array('uid'=>$send_uid,'content_id'=>$send_content_id,'status'=>'0','create_time'=>gmtTime()))->add();
                        //发送失败则用户数 +1
                        $fail_count++;
                }
                else 
                {
                        //更新邮件发送次数
                        D('mail_rss_send_log')->data(array('uid'=>$send_uid,'content_id'=>$send_content_id,'status'=>'1','create_time'=>gmtTime()))->add();
                        D('mail_rss_category')->where('cate_id='.$send_cate_id)->save(array('last_send_time'=>  gmtTime()));
                        D('smtp')->query('update '.FDB::table('smtp').' set batch_count = batch_count+1 where id='.$stmp_arr['id']);
                }
            }
            //失败量小于总数量，视为成功！
            if ($fail_count<$user_count)
            {
                    D('mail_rss_content')->where('id='.$mail_rss_content['id'])->save(array('status'=>'1')); 
                    $this->assign('jumpUrl', Cookie::get ( '_currentUrl_' ) );
                    $this->success ('发送总数：'.$user_count.',成功：'.($user_count-$fail_count));
            }
            else
            {
                    $this->error (L('ADD_ERROR'));
            }
        }
}
function getSendLinks($id){
    $links .= ' <a href="'.U('MailRssContent/sendMail',array('id'=>$id)).'">'.L('SEND_MAIL').'</a>';
    return trim($links);
}
?>
