<?php
return array(
	'USERGROUP'	=>	'会员组',
	'USERGROUP_INDEX'	=>	'会员组列表',
	'USERGROUP_ADD'=>'添加会员组',
	'USERGROUP_EDIT'=>'编辑会员组',
	'NAME'=>'名称',
	'TYPE'=>'类型',
	'TYPE_SYSTEM'=>'系统',
	'TYPE_USER'=>'会员',
	'IS_SPECIAL'=>'特殊会员组',
	'IS_SPECIAL_TIP'=>'特殊会员组不进行集分宝等级和佣金返现',
	'ACCESS'=>'前台权限设置',
	'CREDITS_RANGE'=>'集分宝范围',
	'GOODS_COMMISSION_SCORE_TYPE'=>'佣金返集分宝比率',
	'GOODS_BUY_SCORE_TYPE'=>'购买返集分宝比率',
	'IS_ADMIN'=>'网站工作人员组',
	'ICON'=>'会员组小图标',
	'ICON_TIP'=>'填写格式：直接输入图片名称即可，如 taobao.gif<br/>存放位置：public/icons目录',
	'RESET_GROUP_ACCESS'=>'覆盖当前会员组下所有会员的权限（注：如果当前会员组下会员太多，执行此覆盖操作可能会引起超时等错误发生）',
	'CREDITS_RANGE_TIP'=>'当集分宝达到此范围，将自动升级为此会员组等级',
	'COMMISSION_RATE_TIP'=>'当会员发布的淘宝推广商品，产生佣金时，返现给发布会员的集分宝百分比',
	'BUY_RATE_TIP'=>'当会员点击淘宝推广商品，成功购买，并产生佣金时，返现给购买会员的集分宝百分比；<br/>当发布商品的会员和购买商品的会员为同一个会员时，只会产生<span style="color:#f00;">购买返集分宝</span>，而不会产生<span style="color:#f00;">佣金返集分宝</span>。',
	'BUY_RATE_TIP_1'=>'未登陆用户显示可获取购买返现比率，只作显示用，未登陆用户不会产生购买返现',

	'TAB_1'=>'组信息',
	'TAB_2'=>'前台权限设置',
	
	'NAME_REQUIRE'=>'会员组名称不能为空',
	'NAME_UNIQUE'=>'会员组名称已经存在',
	
	'GROUP_EXIST_USER'=>'会员组下存在会员,不能进行删除',
	'GROUP_SYSTEY_DEL'=>'系统内置会员组,不能进行删除',
    

	'GOODS_BUY_SCORE_TYPE_0'=>'不返集分宝',
	'GOODS_BUY_SCORE_TYPE_1'=>'根据成交金额',
        'GOODS_BUY_SCORE_TYPE_2'=>'根据佣金金额',
    
        'GOODS_COMMISSION_SCORE_RATE'=>'佣金返集分宝',
        'GOODS_BUY_SCORE_RATE'=>'购买返集分宝',
);
return $array;
?>