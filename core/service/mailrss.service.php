<?php
class MailRssService{
    
    /**
     * 获取订阅列表
     * @param array $limit
     * @param array $where
     * @return array 
     */
    function getRssCate($type=0,$limit=null){
        global $_FANWE;
        $key = 'mailrss/cate/catelist_'.$type.'_'.$_FANWE['uid'];
        //$data = getCache($key);
        if($data === null){
            $count_sql = 'SELECT count(*) FROM '.FDB::table('mail_rss_category').' WHERE status=1 AND parent_id>0 ';
            $data['count']  = FDB::resultFirst($count_sql);

            switch ($type)//0所有、1未订阅、2已订阅
            {
                case 0:
                    $sql = 'SELECT rc.*,(SELECT COUNT(*) FROM '.FDB::table('mail_rss_user').' ru WHERE ru.cate_id = rc.cate_id ) as rss_count FROM '.FDB::table('mail_rss_category').' rc WHERE status=1 AND parent_id>0 ORDER BY sort ASC,cate_id DESC';
                    break;
                case 1:
                    $sql = 'SELECT rc.*,(SELECT COUNT(*) FROM '.FDB::table('mail_rss_user').' ru WHERE ru.cate_id = rc.cate_id ) as rss_count FROM '.FDB::table('mail_rss_category').' rc WHERE status=1 AND parent_id>0 AND cate_id not in (SELECT cate_id FROM '.FDB::table('mail_rss_user').' WHERE uid ='.$_FANWE['uid'].' ) ORDER BY sort ASC,cate_id DESC';
                    break;
                case 2:
                    $sql = 'SELECT rc.*,(SELECT COUNT(*) FROM '.FDB::table('mail_rss_user').' ru WHERE ru.cate_id = rc.cate_id ) as rss_count FROM '.FDB::table('mail_rss_category').' rc WHERE status=1 AND parent_id>0 AND cate_id in (SELECT cate_id FROM '.FDB::table('mail_rss_user').' WHERE uid ='.$_FANWE['uid'].' ) ORDER BY sort ASC,cate_id DESC';
            }   
            $data = FDB::fetchAll($sql);        
            setCache($key,$data,600);
        }
        return $data;
    }
    
    function getRssCateById($id){
        global $_FANWE;
        $sql = 'SELECT rc.*,(SELECT COUNT(*) FROM '.FDB::table('mail_rss_user').' ru WHERE ru.cate_id = rc.cate_id AND ru.uid = '.$_FANWE['uid'].' ) as is_rss  FROM '.FDB::table('mail_rss_category').' rc WHERE status=1 AND parent_id>0 AND cate_id = '.$id;
        return FDB::fetchFirst($sql); 
    }
    
    function getRssUser($limit=null,$where = null){
        global $_FANWE;
        $key = 'mailrss/cate/rssuser';
        $data = getCache($key);
        if($data === null){
             $sql = 'SELECT ru.*,u.user_name,rc.short_name FROM '.FDB::table('mail_rss_user').' ru 
                 LEFT JOIN '.FDB::table('user').' u ON u.uid = ru.uid  
                 LEFT JOIN '.FDB::table('mail_rss_category').' rc on rc.cate_id = ru.cate_id where rc.status=1 limit '.implode(',', $limit);
             $data['list'] = FDB::fetchAll($sql);
             setCache($key,$data,600);
        }
        return $data;
        
    }
    
    function saveRss($data){
        if(FDB::resultFirst('SELECT COUNT(*) from '.FDB::table('mail_rss_user').' WHERE uid='.$data['uid'].' and cate_id = '.$data['cate_id']))
                return false;
        
        if($id = FDB::insert('mail_rss_user',$data,true)){
            //更新订阅数量
            FDB::query('update '.FDB::table('mail_rss_category').' set rss_count = rss_count+1 where cate_id = '.$data['cate_id']);
            return true;
        }else{
            return false;
        }
    }
    
    function removeRss($cate_id,$uid){
        if($uid>0 && $cate_id>0){
            FDB::delete('mail_rss_user',array('uid'=>$uid,'cate_id'=>$cate_id));
            FDB::query('update '.FDB::table('mail_rss_category').' set rss_count = rss_count-1 where cate_id= '.$cate_id);
            return true;
        }else{
            return false;
        }
         
    }
    function checkRssSn($sn){
        return FDB::fetchFirst('SELECT * FROM '.FDB::table('mail_rss_user').' WHERE rss_sn=\''.$sn.'\'');
    }
}
?>
