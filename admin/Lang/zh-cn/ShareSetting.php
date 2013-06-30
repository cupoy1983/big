<?php
$array = array(
	'SHARESETTING'=>'分享配置管理',
	'SHARESETTING_INDEX'=>'分享配置',
	
	'SECONDS' => '秒',
	
	'SHARE_INTERVAL_TIME'=>'发布分享、回复、评论间隔时间',
	'SHARE_CACHE_TIME'=>'分享相关页面缓存过期时间',
	'SHARE_GOODS_COUNT'=>'一个分享最大商品数量',
	'SHARE_PIC_COUNT'=>'一个分享最大图片数量',
	'SHARE_IS_TAG'=>'用户是否可以设置分享标签',
	'SHARE_TAG_COUNT'=>'一个分享最大标签数量',
	'SHARE_IS_CATE'=>'用户是否可以设置分享分类',
	'SHARE_IS_CATE_BY_TAGS'=>'是否开启商品分享自动分类',
	'SHARE_IS_CATE_BY_TAGS_TIP'=>'当无法根据商品分类得到所属分享分类时，可开启此设置，会根据商品名称分词后，<br/>匹配分享分类的标签来得到分享分类（此方式得到的分享分类准确度不高）。',
	
	'SHARE_CHECK_TYPE'=>'分享审核',
	'SHARE_CHECK_TYPE_0'=>'所有分享都不审核',
	'SHARE_CHECK_TYPE_1'=>'所有分享都需要审核',
	'SHARE_CHECK_TYPE_2'=>'文字分享需要审核',
	'SHARE_CHECK_TYPE_3'=>'有图分享(商品、图片)需要审核',
	'SHARE_CHECK_TYPE_4'=>'商品分享需要审核',
	'SHARE_CHECK_TYPE_5'=>'图片分享需要审核',
	
	'SHARE_CHECK_TYPE_TIP'=>'需要审核的分享如果未通过审核，只在会员自己的会员中心显示<br/>不需要审核的分享，会将审核状态默认设为已审核',
	
	'SHARE_GOODS_SHOW_TYPE'=>'逛街商品显示方式',
	'SHARE_GOODS_SHOW_TYPE_0'=>'显示重复商品',
	'SHARE_GOODS_SHOW_TYPE_1'=>'重复商品按排序只显示第一个',
	
	'SHARE_PB_LOAD_COUNT'=>'分享瀑布流每页加载次数',
	'SHARE_PB_ITEM_COUNT'=>'分享瀑布流每次加载数量',
	
	'SHARE_IMAGE_SAVE_TYPE'=>'分享图片方式',
	'SHARE_IMAGE_SAVE_TYPE_1'=>'全部保存到服务器',
	'SHARE_IMAGE_SAVE_TYPE_0'=>'优先使用网络图片',
	'SHARE_IMAGE_SAVE_TYPE_TIP'=>'当使用网络图片时，前台图片有可能因规格大小缩放引起图片变形',
	
	'IS_GOODS_DELIST'=>'是否开启商品下架功能',
	'IS_GOODS_DELIST_0'=>'关闭',
	'IS_GOODS_DELIST_1'=>'开启',
	'IS_GOODS_DELIST_TIP'=>'注：删除商品时，将同时删除与商品关联的分享数据',
	
	'SHARE_GOODS_SHOW_TYPE_TIP'=>'当设为不显示重复商品时，因为去重，逛街页面的分享显示数量有可能会不一致',
);
return $array;
?>