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
 * 首页分类分享设置模型
 +------------------------------------------------------------------------------
 */
class IndexCateShareModel extends CommonModel
{
	public $_validate = array(
		array('name','require','{%NAME_REQUIRE}',0),
		array('share_id','','{%SHARE_UNIQUE}',0,'unique',1),
	);
}
?>