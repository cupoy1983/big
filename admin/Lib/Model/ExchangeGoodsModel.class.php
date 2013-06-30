<?php
class ExchangeGoodsModel extends CommonModel
{
	protected $_auto = array( 
		array('status','1'),  // 新增的时候把status字段设置为1	
	);
}
?>