<?php
if($_FANWE['uid'] == 0)
	exit;
	
if(empty($_FANWE['request']['reason']))
	exit;

$id = intval($_FANWE['request']['id']);
if($id > 0)
	$goods = FS('Exchange')->getById($id);

$result['status'] = 0;

if($_FANWE['uid'] > 0 && $goods && ($goods['apply_type'] == 0 || $goods['apply_type'] == 2))
{
	if($goods['begin_time'] > 0 && $goods['begin_time'] > TIME_UTC)
	{
		$result['msg'] = lang('exchange','begin_time_error');
		outputJson($result);
	}
	
	if($goods['end_time'] > 0 && $goods['end_time'] < TIME_UTC)
	{
		$result['msg'] = lang('exchange','end_time_error');
		outputJson($result);
	}
	
	if($goods['stock'] <= intval($goods['buy_count']))
	{
		$result['msg'] = lang('exchange','apply_stock_error');
		outputJson($result);
	}
	
	$user_num = (int)FDB::resultFirst('SELECT COUNT(*) FROM '.FDB::table('order').' WHERE rec_id = '.$id.' AND uid = '.$_FANWE['uid']);
	if($user_num > 0)
	{
		$result['msg'] = lang('exchange','apply_user_num_error1');
		outputJson($result);
	}
	
	$data['zip'] = trim($_FANWE['request']['zip']);
	$data['address'] = trim($_FANWE['request']['address']);
	$data['email'] = trim($_FANWE['request']['email']);
	$data['mobile_phone'] = trim($_FANWE['request']['mobile']);
	$data['fax_phone'] = trim($_FANWE['request']['fax']);
	$data['fix_phone'] = trim($_FANWE['request']['fix']);
	$data['qq'] = trim($_FANWE['request']['qq']);
	
	$consignment = $data;
	$consignment['uid'] = $_FANWE['uid'];
	
	if(!empty($consignment['address']))
	{
		FDB::insert('user_consignee',$consignment,false,true);
	}
	
	$data['memo'] = htmlspecialchars(trim(cutstr($_FANWE['request']['memo'],500,'')));
	$data['reason'] = htmlspecialchars($_FANWE['request']['reason']);
	$data['data_name'] = $goods['name'];
	$data['sn'] = fToDate(TIME_UTC,'ymdHis').mt_rand(0,100);
	$data['order_type'] = 1;
	$data['goods_status'] = 0;
	$data['order_score'] = $goods['integral'];
	$data['data_num'] = 1;
	$data['uid'] = $_FANWE['uid'];
	$data['user_name'] = $_FANWE['user']['user_name'];
	$data['rec_id'] = $id;
	$data['create_time'] = TIME_UTC;
	$data['update_time'] = TIME_UTC;
	
	$order_id = FDB::insert('order',$data,true);
	
	while(intval($order_id)==0)
	{
		$order['sn'] = fToDate(TIME_UTC,'ymdHis').mt_rand(0,100);
		$order_id = FDB::insert('order',$data,true);
	}
	
	FDB::query('UPDATE '.FDB::table('exchange_goods').' SET apply_count = apply_count + 1 WHERE id = '.$id);
	$result['status'] = 1;
	
	$share = array();
	$share['share']['uid'] = $_FANWE['uid'];
	$share['share']['rec_id'] = $id;
	$share['share']['type'] = 'trial_apply';
	$share['share']['title'] = addslashes($goods['name']);
	$share['share']['content'] = htmlspecialchars($_FANWE['request']['reason']);
	$share = FS('Share')->save($share);
	
	$post = array();
	$post['eid'] = $id;
	$post['share_id'] = $share['share_id'];
	$post['uid'] = $_FANWE['uid'];
	$post['type'] = 0;
	$post['content'] = htmlspecialchars($_FANWE['request']['reason']);
	$post['create_time'] = TIME_UTC;
	FDB::insert('exchange_post',$post);
	
	FS('Exchange')->setApplyCache($id);
	$result['msg'] = lang('exchange','apply_success');
}
else
	$result['msg'] = lang('exchange','apply_error');
outputJson($result);
?>