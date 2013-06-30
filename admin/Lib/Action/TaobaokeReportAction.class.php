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
 淘宝客报表
 +------------------------------------------------------------------------------
 */
class TaobaokeReportAction extends CommonAction
{
	public function index()
	{
		$where = '';
		$parameter = array();
		$outer_code = trim($_REQUEST['outer_code']);
		$day_time_str = trim($_REQUEST['day_time']);
		$day_time = !empty($day_time_str) ? strZTime($day_time_str) : 0;

		if(!empty($outer_code))
		{
			$this->assign("outer_code",$outer_code);
			$parameter['outer_code'] = $outer_code;
            $where .= " AND outer_code = '$outer_code'";
		}
		
		if ($day_time > 0)
		{
			$this->assign("day_time",$day_time_str);
			$parameter['day_time'] = $day_time_str;
			$where .= " AND pay_day = '".$day_time."'";
		}
	
		$model = M();

		if(!empty($where))
			$where = str_replace('WHERE AND','WHERE','WHERE'.$where);

		$sql = 'SELECT COUNT(id) AS tcount FROM '.C("DB_PREFIX").'taobaoke_report '.$where;
		$count = $model->query($sql);
		$count = $count[0]['tcount'];

		$sql = 'SELECT id,item_title,item_num,pay_price,real_pay_fee,CONCAT(commission_rate,\'%\') AS commission_rate,commission,outer_code,pay_time FROM '.C("DB_PREFIX").'taobaoke_report '.$where;
		$this->_sqlList($model,$sql,$count,$parameter,'id');
		$this->display();
		return;
	}

	function collect()
	{
		$isInit =  (int)$_REQUEST['isInit'];
		if($isInit == 1)
		{
			$date =  strZTime($_REQUEST['date']);
			$page = 1;
			$tips = '开始获取第 1 页报表';
		}
		else
		{
			$date = (int)$_REQUEST['date'];
			$page = (int)$_REQUEST['page'];

			$tips = '开始获取第 '.$page.' 页报表';
		}
		$this->assign("tips",$tips);
		
		ob_start();
		ob_end_flush(); 
		ob_implicit_flush(1);
		$this->display();
		
		vendor('common');
		include fimport('class/taobao','api');
		$api = new Taobao();
		$result = $api->collectReport($date,$page);
		if($result == 0)
			echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('TaobaokeReport/collect',array('date'=>$date,'page'=>$page + 1)).'";},500);</script>');
		elseif($result == 1)
		{
			echoFlush('<script type="text/javascript">setTimeout(function(){$("#notice").html($("#notice").html() + "<br/>采集成功");},500);</script>');
			echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('TaobaokeReport/index').'";},1500);</script>');
		}
		else
		{
			echoFlush('<script type="text/javascript">setTimeout(function(){$("#notice").html($("#notice").html() + "<br/>API返回数据错误");},500);</script>');
			echoFlush('<script type="text/javascript">setTimeout(function(){location.href="'.U('TaobaokeReport/index').'";},1500);</script>');
		}
    }
}
?>