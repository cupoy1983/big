<?php
if($_FANWE['uid'] == 0)
	exit;

$id = intval($_FANWE['request']['id']);
if($id > 0)
	$goods = FS('Exchange')->getById($id);

$result['status'] = 0;

if($_FANWE['uid'] > 0 && $goods && $goods['apply_type'] > 0)
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
	
	$score = FS("User")->getUserScore($_FANWE['uid']);
	if($score < 0)
		exit;
	
	if($score < $goods['integral'])
	{
		$result['msg'] = lang('exchange','integral_error');
		outputJson($result);
	}
	
	if($goods['stock'] <= intval($goods['buy_count']))
	{
		$result['msg'] = lang('exchange','stock_error');
		outputJson($result);
	}
	
	$apply = FDB::fetchFirst('SELECT * FROM '.FDB::table('order').' WHERE order_type = 1 AND rec_id = '.$id.' AND uid = '.$_FANWE['uid']);
	if($apply)
	{
		FDB::delete('order','id = '.$apply['id']);
		FDB::query('UPDATE '.FDB::table('exchange_goods').' SET apply_count = apply_count - 1 WHERE id = '.$id);
	}
	
	$user_num = FDB::resultFirst('SELECT SUM(data_num) FROM '.FDB::table('order').' WHERE rec_id = '.$id.' AND uid = '.$_FANWE['uid']);
	if($user_num >= intval($goods['user_num']))
	{
		$result['msg'] = lang('exchange','user_num_error');
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
	$data['data_name'] = $goods['name'];
	$data['sn'] = fToDate(TIME_UTC,'ymdHis').mt_rand(0,100);
	$data['order_type'] = 0;
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
	//库存为零，则将该商品下架
	if($goods['stock'] <= intval($goods['buy_count']) + 1){
		$status = ", status = 0 ";
	}else{
		$status = "";
	}
	FDB::query('UPDATE '.FDB::table('exchange_goods').' SET buy_count = buy_count + 1 '.$status.'WHERE id = '.$id);
	
	FS("User")->updateUserScore($_FANWE['uid'],"exchange","submit",addslashes($goods['name']),$order_id,0 - intval($data['order_score']));
	$result['status'] = 1;
	
	$share = array();
	$share['share']['uid'] = $_FANWE['uid'];
	$share['share']['rec_id'] = $id;
	$share['share']['content'] = sprintf(lang('exchange','exchang_share'),
		FU('exchange/info',array('id'=>$id)),
		addslashes($goods['name']));
	FS('Share')->save($share);
	
	FS('Exchange')->setApplyCache($id);
	$result['msg'] = lang('exchange','exchange_success');
}
else
	$result['msg'] = lang('exchange','goods_error');
outputJson($result);
?>