<?php
return array(
	'USERAUTHORITY'	=>	'会员权限',
	'USERAUTHORITY_EDIT'=>'设置会员权限',
	'MODULE_NAME'=>'模块',
	'ACTION_NAME'=>'操作',
	
	'ACTION_BEST'=>'推荐',
	'ACTION_TOP'=>'置顶',
	'ACTION_EDIT'=>'编辑',
	'ACTION_STATUS'=>'审核',
	'ACTION_DELETE'=>'删除',
	'ACTION_INDEX'=>'首页显示',
	'ACTION_DAREN'=>'取消选款师'
	,
	'ACTION_HOT'=>'热门',
	'ACTION_EVENT'=>'活动',
	'ACTION_SHARE_BEST'=>'精选搭配',

	'ACTION_FLASH'=>'轮播',
	
	
	'AUTHORITYS'=>array(
		'share'=>array(
			'name'=>'分享',
			'actions'=>array('status','edit','delete')
		),
		'club'=>array(
			'name'=>'主题',
			'actions'=>array('best','top','edit','delete')
		),
		'event'=>array(
			'name'=>'话题',
			'actions'=>array('hot','event','delete')
		),
		'album'=>array(
			'name'=>'杂志社',
			'actions'=>array('best','flash','edit','delete')
		),
	),
);
return $array;
?>