<?php
class CheckinModule 
{
	
	 const qiandao_jifen = 1;   //签到送集分宝
	 const firstAwardjf = 50;      //连续签到 额外送多少集分宝
	 const award = 100;   //连续签到一个月可额外奖励多少集分宝
	
    public function index(){    
        global $_FANWE;
        $_FANWE['nav_title'] = "签到";
        $timedate = date("Ymd");
        $where = " WHERE times=1 AND FROM_UNIXTIME(time,'%Y%m%d')='{$timedate}'";
        $sql = 'SELECT COUNT(id) FROM '.FDB::table("qiandao").$where;
        $tdcount = FDB::resultFirst($sql);
        include template('page/u/u_checkin');
        display();      
    }
    
    public function checkin_ajax(){
        global $_FANWE;
        $total = date('t');
        $type = $_REQUEST['type'];
        if($_FANWE['uid']<=0){
        	echo json_encode(array('ret'=>'nologin','tip'=>'您还没有登录，请先登录！！'));
        	exit;
        }
        //type=2, ajax签到
        if($type==2){
            $timedate = date("Ymd");
            $where = " WHERE uid={$_FANWE['uid']} AND times=1 AND FROM_UNIXTIME(time,'%Y%m%d')='{$timedate}'";
            $sql = "SELECT id FROM ".FDB::table("qiandao").$where;
            $qiandao = FDB::fetchFirst($sql);
            
            if($qiandao['id']>0){
                echo json_encode(array('ret'=>'fail','tip'=>'今天您已经签到了！！'));
                exit;
            }else{
                //更新集分宝
                FDB::query('UPDATE '.FDB::table("user").' SET credits = credits+'.self::qiandao_jifen.' WHERE uid = '.$_FANWE['uid']);
                $qindao_data = array();
                $qindao_data['time'] = time();
                $qindao_data['uid']  = $_FANWE['uid'];
                $qindao_data['user_name'] = $_FANWE['user_name'];
                $qindao_data['times'] = 1;
                $qindao_data['jifen'] = self::qiandao_jifen;
                FDB::insert('qiandao',$qindao_data,true); 
                
                //如果连续签到10次 开始
                $timedate = date("Ym");
                $sql = "SELECT id FROM ".FDB::table("user_medal")." WHERE uid={$_FANWE['uid']} AND mid=4";
                $media = FDB::fetchFirst($sql);
                if(empty($media)){
                	$where = " WHERE uid={$_FANWE['uid']} AND times=1 AND FROM_UNIXTIME(time,'%Y%m')='{$timedate}'";
                	$sql = 'SELECT id,time FROM '.FDB::table("qiandao").$where;
                	$qiandaoAll = FDB::fetchAll($sql);
                	$date = array();
                	foreach($qiandaoAll as $key=>$value){
                		$date[$key] = date('Y-m-d',$value['time']);
                	}
                	$flag = CheckinModule::checkContinuous($date);
                	if($flag==9){
                		$sql = 'UPDATE '.FDB::table("user").' SET credits = credits+'.self::firstAward.' WHERE uid = '.$_FANWE['uid'];
                		FDB::query($sql);
                		$timedate = date("Ymd");
                		$qdsql = 'UPDATE '.FDB::table("qiandao").
                		" SET jifen = jifen+".self::firstAward." WHERE uid = '{$_FANWE['uid']}' AND FROM_UNIXTIME(time,'%Y%m%d')='{$timedate}'";
                		FDB::query($qdsql);
                	}
                	if($flag>=9){
                		CheckinModule::songxunzhang();
                	}
                }
                //如果连续签到10次 结束 
                
                //如果连续签到一个月开始
                
                //统计集分宝
                $timedate = date("Ym");
                $where = " WHERE uid={$_FANWE['uid']} AND times=1";
                $sql = 'SELECT SUM(jifen) FROM '.FDB::table("qiandao").$where;
                $totalPoints = FDB::resultFirst($sql);
                $sql = 'SELECT SUM(jifen) FROM '.FDB::table("qiandao").$where." AND FROM_UNIXTIME(time,'%Y%m')='{$timedate}'";
                $upoints = FDB::resultFirst($sql);
                
                $timedate = date("Ym");
                $where = " WHERE  uid={$_FANWE['uid']} AND times=1 AND FROM_UNIXTIME(time,'%Y%m')='{$timedate}'";
                $sql = 'SELECT COUNT(id) FROM '.FDB::table("qiandao").$where;
                $count = FDB::resultFirst($sql);
                $lastDay = $total - $count;
                //如果连续签到一个月,送集分宝
                if($lastDay==0){
                    //更新集分宝
                    FDB::query('UPDATE '.FDB::table("user").' SET credits = credits+'.self::award.' WHERE uid = '.$_FANWE['uid']);
                    $timedate = date("Ymd");
                    $qdsql = 'UPDATE '.FDB::table("qiandao")." SET jifen = jifen+".self::award." WHERE uid = '{$_FANWE['uid']}' AND FROM_UNIXTIME(time,'%Y%m%d')='{$timedate}'";
                    FDB::query($qdsql);
                      echo json_encode(array(
                        'ret'=>'success',
                        'tip'=>"您全月".$total."天连续签到，已获取额外送您的".self::award."集分宝全勤奖啦！",
                        'getjifen'=>self::qiandao_jifen,
                        'upoints'=>$upoints + self::award,
                      	'totalPoints'=>$totalPoints
                        )
                    );     
                }else{
                    echo json_encode(array(
                        'ret'=>'success',
                        'tip'=>"您已经连续签到{$count}天，该月再签到{$lastDay}天就可以领取".self::award."集分宝哦！",
                        'getjifen'=>self::qiandao_jifen,
                        'upoints'=>$upoints,
                        'totalPoints'=>$totalPoints
                        )
                    );
                }
                //如果连续签到一个月结束
            }
        //type=1, 查询当日是否签到
        }elseif($type==1){
            $timedate = date("Ymd");
            $where = " WHERE uid={$_FANWE['uid']} AND times=1 AND FROM_UNIXTIME(time,'%Y%m%d')='{$timedate}'";
            $sql = "SELECT id FROM ".FDB::table("qiandao").$where;
            $qiandao = FDB::fetchFirst($sql);
            if($qiandao['id']>0){
                echo json_encode(array('ret'=>'fail'));
                exit;
            }else{
                echo json_encode(array('ret'=>'success'));
                exit;
            }
        }
    }
    
    //连续签到10天送勋章 
    private function songxunzhang(){
        global $_FANWE;
        $media_data = array();
        $media_data['create_time'] = time();
        $media_data['uid']  = $_FANWE['uid'];
        $media_data['type'] = 0;
        $media_data['mid'] = 4;
        $media_data['deadline'] = 0;
        FDB::insert('user_medal',$media_data,true);
        return true;   
    }
    
    //检查是否连续
    private function checkContinuous($arr){
    	$flag = 0;
    	function compare($x,$y){
    		return (strtotime($x)-strtotime($y));
    	}
    	uasort($arr,'compare');
    	$arr = array_slice($arr,0);
    	for($i = 1;$i<count($arr);$i++){
    		if((strtotime($arr[$i])-strtotime($arr[0]))==($i*24*60*60)){
    			$flag++;
    		}else{
    			break;
    		}
    	}
    	return $flag;
    }
    
    //查询每月签到次数
    public function checkintimes(){
        global $_FANWE;
        $type = $_REQUEST['type'];
        //当月总天数
        $total = date('t');
        //if type=1指定查询月份 else 不指定
        if($type==1){
        	$month = $_REQUEST['month'];
        	$year = $_REQUEST['year'];
        	$timedate = ($year.$month)==''?date("Ym"):$year.$month;
        }else{
        	$timedate = date("Ym");
        }
       
        $where = " WHERE uid={$_FANWE['uid']} AND times=1 AND FROM_UNIXTIME(time,'%Y%m')='{$timedate}'";
        $sql = "SELECT id,time FROM ".FDB::table("qiandao").$where;
        $qiandaoAll = FDB::fetchAll($sql);
        $count = array();
        foreach($qiandaoAll as $key=>$value){
            $count[$key]['time'] = date('Y-m-d',$value['time']);
        }
        echo json_encode(array('ret'=>'success','data'=>$count,'checkintimes'=>count($count),'lasttimes'=>$total-count($count)));
        exit; 
    }
}

?>