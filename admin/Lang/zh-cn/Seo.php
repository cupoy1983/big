<?php
$array = array(
	'SEO'=>'SEO配置管理',
	'SEO_INDEX'=>'SEO配置',
	'SEO_DOWN'=>'下载重写规则',
	
	'TITLE' => '页面标题',
	'KEYWORDS' => 'META关键字',
	'DESCRIPTION'=>'META描述',
	'REWRITE'=>'重写规则',
	'GREWRITE'=>'页面重写规则',
	'DOWN'=>'下载重写规则',
	'DOWN1'=>'下载',
	
	'RULE_IIS6_2'=>'适用于IIS6 Rewrite3<span style="color:#f00;">以下</span>，下载后请覆盖或者修改与你网站对应的有效规则文件（默认在 rewrite\httpd.ini）',
	'RULE_IIS6_3'=>'适用于IIS6 Rewrite3，下载后请覆盖或者修改与你网站对应的有效规则文件（默认在 .htaccess）',
	'RULE_IIS7'=>'适用于IIS6<span style="color:#f00;">以上</span>，下载后请覆盖或者修改与你网站对应的有效规则文件（默认在 web.config）',
	'RULE_APACHE'=>'适用于Apache，下载后请覆盖或者修改与你网站对应的有效规则文件（默认在 .htaccess）',
	'RULE_NGINX'=>'适用于Nginx，下载后请覆盖或者修改与你网站对应的有效规则文件（默认在 nginx.conf）',
	
	'BOOK'=>'逛街',
	'NOTE'=>'分享',
	'ALBUM'=>'杂志社',
	'LOOK'=>'晒货',
	'DAPEI'=>'搭配',
	'GROUP'=>'小组',
	'TOPIC'=>'主题',
	'DAREN'=>'达人',
	'SHOP'=>'好店',
	'EXCHANGE'=>'试用',
	
	'TIPS'=>'说明：&nbsp;<span style="color:#ff0000">如果有修改重新规则，请点击【下载重写规则】，并下载对应的规则文件替换当前网站的规则文件</span><br/>
	　　　&nbsp;页面标题、META关键字、META描述，每行代表一条规则(输入框内 敲击回车键 换行)。<br/>
	　　　&nbsp;如果第一条规则里的{XXX}都有值，则使用第一条规则，否则匹配下一条规则，以此类推。如果都未匹配到，则使用最后一条规则。<br/>
	　　　&nbsp;<span style="color:#0000ff">{GLOBAL_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示后台系统设置中的&nbsp;站点名称；<br/>
	　　　&nbsp;<span style="color:#0000ff">{GLOBAL_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示后台系统设置中的&nbsp;站点标题；<br/>
	　　　&nbsp;<span style="color:#0000ff">{GLOBAL_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;表示后台系统设置中的&nbsp;SEO关键词；<br/>
	　　　&nbsp;<span style="color:#0000ff">{GLOBAL_DESCRIPTION}</span>&nbsp;表示后台系统设置中的&nbsp;SEO描述；',
	
	'BOOK_GREWRITE'=>'重写对应页面 %sbook.php',
	'BOOK_SHOPPING_TIPS'=>'<span style="color:#ff0000">{CATE_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类&nbsp;分类名称；<br/>
                <span style="color:#ff0000">{TAG}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示当前页面搜索标签；<br/>
		<span style="color:#ff0000">{SHORT_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类&nbsp;副名称；<br/>
		<span style="color:#ff0000">{DESC}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类&nbsp;分类描述；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类&nbsp;分类SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类&nbsp;分类SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;表示分享分类&nbsp;分类SEO描述；',
	
	'BOOK_CATE_TIPS'=>'<span style="color:#ff0000">{CATE_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类中的&nbsp;分类名称；<br/>
		<span style="color:#ff0000">{SHORT_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类中的&nbsp;副名称；<br/>
		<span style="color:#ff0000">{DESC}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类中的&nbsp;分类描述；<br/>
		<span style="color:#ff0000">{TAG}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示当前页面搜索标签；<br/>
		<span style="color:#ff0000">{GROUP_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示当前页面分组名称；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类中的&nbsp;分类SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;表示分享分类中的&nbsp;分类SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;表示分享分类中的&nbsp;分类SEO描述；',
	'BOOK_SHOPPING_REWRITE'=>'重写对应页面 %sbook.php?action=shopping',
	'BOOK_CATE_REWRITE'=>'重写对应页面 %sbook.php?action=cate&cate=xxx',
	
	'NOTE_GREWRITE'=>'重写对应页面 %snote.php',
	'NOTE_ALL_TIPS'=>'<span style="color:#ff0000">{TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;分享标题；(有可能为空)<br/>
		<span style="color:#ff0000">{CONTENT}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;分享内容；(只取前80字)<br/>
		<span style="color:#ff0000">{TAGS}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;分享标签；<br/>
		<span style="color:#ff0000">{USER_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;发布会员名称；<br/>
		<span style="color:#ff0000">{BEST_DESC}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;推荐说明；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;&nbsp;&nbsp;表示分享&nbsp;SEO描述；<br/>
		<span style="color:#ff0000">{CATE_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;所属分类名称；<br/>
		<span style="color:#ff0000">{ALBUM_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;所属杂志社名称；(只有分享属于某个杂志社时才有值)<br/>
		<span style="color:#ff0000">{GOODS_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;商品标题；(只在商品分享详细页中有值)<br/>
		<span style="color:#ff0000">{GOODS_ALT}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;商品描述；(只在商品分享详细页中有值)<br/>
		<span style="color:#ff0000">{PHOTO_ALT}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示分享&nbsp;图片描述；(只在图片分享详细页中有值)',
	'NOTE_ALL_REWRITE'=>'重写对应页面 %snote.php?action=xxx&sid=xxx',
	
	'ALBUM_GREWRITE'=>'重写对应页面 %salbum.php',
	'ALBUM_INDEX_TIPS'=>'',
	'ALBUM_INDEX_REWRITE'=>'重写对应页面 %salbum.php?action=index',
	
	'ALBUM_CATEGORY_TIPS'=>'<span style="color:#ff0000">{NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社分类&nbsp;分类名称；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社分类&nbsp;SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社分类&nbsp;SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;表示杂志社分类&nbsp;SEO描述；',
	'ALBUM_CATEGORY_REWRITE'=>'重写对应页面 %salbum.php?action=category&id=xxx',
	
	'ALBUM_SHOW_TIPS'=>'<span style="color:#ff0000">{TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社&nbsp;名称；<br/>
		<span style="color:#ff0000">{TAGS}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社&nbsp;时尚元素；<br/>
		<span style="color:#ff0000">{CONTENT}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社&nbsp;介绍；(只取前80字)<br/>
		<span style="color:#ff0000">{USER_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社&nbsp;创建会员名称；<br/>
		<span style="color:#ff0000">{CATE_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社&nbsp;所属分类名称；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社&nbsp;SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示杂志社&nbsp;SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;&nbsp;&nbsp;表示杂志社&nbsp;SEO描述；',
	'ALBUM_SHOW_REWRITE'=>'重写对应页面 %salbum.php?action=show&id=xxx',
	
	'LOOK_GREWRITE'=>'重写对应页面 %slook.php',
	'LOOK_INDEX_TIPS'=>'',
	'LOOK_INDEX_REWRITE'=>'重写对应页面 %slook.php?action=index',
	
	'DAPEI_GREWRITE'=>'重写对应页面 %sdapei.php',
	'DAPEI_INDEX_TIPS'=>'',
	'DAPEI_INDEX_REWRITE'=>'重写对应页面 %sdapei.php?action=index',
	
	'GROUP_GREWRITE'=>'重写对应页面 %sgroup.php',
	'GROUP_INDEX_TIPS'=>'',
	'GROUP_INDEX_REWRITE'=>'重写对应页面 %sgroup.php?action=index',
	
	'GROUP_DETAIL_TIPS'=>'<span style="color:#ff0000">{NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示小组&nbsp;小组名称；<br/>
		<span style="color:#ff0000">{TAGS}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示小组&nbsp;标签；<br/>
		<span style="color:#ff0000">{CONTENT}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示小组&nbsp;介绍；(只取前80字)<br/>
		<span style="color:#ff0000">{USER_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示小组&nbsp;创建会员名称；<br/>
		<span style="color:#ff0000">{CATE_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示小组&nbsp;分类名称；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示小组&nbsp;SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;表示小组&nbsp;SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;表示小组&nbsp;SEO描述；',
	'GROUP_DETAIL_REWRITE'=>'重写对应页面 %sgroup.php?action=detail&fid=xxx',
	
	'TOPIC_GREWRITE'=>'重写对应页面 %stopic.php',
	'TOPIC_DETAIL_TIPS'=>'<span style="color:#ff0000">{TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示主题&nbsp;标题；<br/>
		<span style="color:#ff0000">{CONTENT}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示主题&nbsp;内容；(只取前80字)<br/>
		<span style="color:#ff0000">{USER_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示主题&nbsp;发布会员名称；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示主题&nbsp;SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示主题&nbsp;SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;&nbsp;&nbsp;表示主题&nbsp;SEO描述；<br/><br/>
		<span style="color:#ff0000">{GROUP_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示所属小组&nbsp;小组名称；<br/>
		<span style="color:#ff0000">{GROUP_USER_NAME}</span>&nbsp;&nbsp;&nbsp;表示所属小组&nbsp;创建会员名称；<br/>
		<span style="color:#ff0000">{GROUP_TAGS}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示所属小组&nbsp;标签；<br/>
		<span style="color:#ff0000">{GROUP_CATE_NAME}</span>&nbsp;&nbsp;&nbsp;表示所属小组&nbsp;分类名称；',
	'TOPIC_DETAIL_REWRITE'=>'重写对应页面 %stopic.php?action=detail&tid=xxx',
	
	'DAREN_GREWRITE'=>'重写对应页面 %sdaren.php',
	'DAREN_INDEX_TIPS'=>'',
	'DAREN_INDEX_REWRITE'=>'重写对应页面 %sdaren.php?action=index',
	
	'DAREN_CATE_TIPS'=>'<span style="color:#ff0000">{NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示达人分类&nbsp;分类名称；<br/>
		<span style="color:#ff0000">{CONTENT}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示达人分类&nbsp;分类说明；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示达人分类&nbsp;SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;表示达人分类&nbsp;SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;表示达人分类&nbsp;SEO描述；',
	'DAREN_CATE_REWRITE'=>'重写对应页面 %sdaren.php?action=cate&id=xxx',
	
	'SHOP_GREWRITE'=>'重写对应页面 %sshop.php',
	'SHOP_INDEX_TIPS'=>'',
	'SHOP_INDEX_REWRITE'=>'重写对应页面 %sshop.php?action=index',
	
	'SHOP_CATE_TIPS'=>'<span style="color:#ff0000">{NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示店铺分类&nbsp;分类名称；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示店铺分类&nbsp;SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;表示店铺分类&nbsp;SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;表示店铺分类&nbsp;SEO描述；',
	'SHOP_CATE_REWRITE'=>'重写对应页面 %sshop.php?action=index&cid=xxx',
	
	'SHOP_SHOW_TIPS'=>'<span style="color:#ff0000">{SHOP_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示店铺&nbsp;名称；<br/>
		<span style="color:#ff0000">{CATE_NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示店铺&nbsp;所属分类名称；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示店铺&nbsp;SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示店铺&nbsp;SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;&nbsp;&nbsp;表示店铺&nbsp;SEO描述；',
	'SHOP_SHOW_REWRITE'=>'重写对应页面 %sshop.php?action=show',
	
	'EXCHANGE_GREWRITE'=>'重写对应页面 %sexchange.php',
	'EXCHANGE_INDEX_TIPS'=>'',
	'EXCHANGE_INDEX_REWRITE'=>'重写对应页面 %sexchange.php?action=index',

	'EXCHANGE_INFO_TIPS'=>'<span style="color:#ff0000">{NAME}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示试用&nbsp;商品名称；<br/>
		<span style="color:#ff0000">{SEO_TITLE}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示试用&nbsp;SEO标题；<br/>
		<span style="color:#ff0000">{SEO_KEYWORDS}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示试用&nbsp;SEO关键字；<br/>
		<span style="color:#ff0000">{SEO_DESCRIPTION}</span>&nbsp;&nbsp;&nbsp;表示试用&nbsp;SEO描述；',
	'EXCHANGE_INFO_REWRITE'=>'重写对应页面 %sexchange.php?action=info&id=xxx',
);
return $array;
?>