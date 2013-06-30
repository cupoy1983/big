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
 商品同义词库
 +------------------------------------------------------------------------------
 */
class GoodsDictionaryAction extends CommonAction
{
	public function index()
	{
		$where = '';
		$parameter = array();
		$word = trim($_REQUEST['word']);
		
		if(!empty($word))
		{
			$where .= " AND word LIKE '%".mysqlLikeQuote($word)."%'";
			$this->assign("word",$word);
			$parameter['word'] = $word;
		}
		
		$model = M();
		
		if(!empty($where))
			$where = str_replace('WHERE AND','WHERE','WHERE'.$where);
		
		$sql = 'SELECT COUNT(DISTINCT id) AS wcount 
			FROM '.C("DB_PREFIX").'goods_dictionary '.$where;

		$count = $model->query($sql);
		$count = $count[0]['wcount'];

		$sql = 'SELECT * FROM '.C("DB_PREFIX").'goods_dictionary '.$where;
		$this->_sqlList($model,$sql,$count,$parameter,'id');
		
		$this->display ();
	}
	
	public function insert()
	{
		$words = trim($_REQUEST['words']);
		$words = explode("\n",$words);

		foreach($words as $word)
		{
			$word = trim($word);
			if(!empty($word))
			{
				$word = explode("=",$word);
				$obj = array();
				$obj['word'] = $word[0];
				$obj['rword'] = $word[1];
				
				$old = D('GoodsDictionary')->where("word = '$obj[word]'")->find();
				if(isset($old['id']))
					D('GoodsDictionary')->where("id = ".intval($old['id']))->save($obj);
				else
					D('GoodsDictionary')->add($obj);
			}
		}
		$this->success (L('ADD_SUCCESS'));
	}
	
	public function import()
	{
		$this->display();
	}
	
	public function save()
	{
		@set_time_limit(0);
		$source = FANWE_ROOT."public/upload/temp/goodsdictionary.txt";
		if(!isset($_FILES['word_file']))
		{
			$this->error (L('WORD_FILE_EMPTY'));
		}
		elseif($_FILES['word_file']['tmp_name'] == 'none')
		{
			$this->error (L('WORD_FILE_ERROR'));
		}
		elseif(!move_uploaded_file($_FILES['word_file']['tmp_name'],$source))
		{
			$this->error (L('WORD_FILE_ERROR'));
		}
		
		$words = file_get_contents($source);
		$words = explode("\n",$words);
		
		foreach($words as $word)
		{
			$word = trim($word);
			if(!empty($word))
			{
				$word = explode("=",$word);
				$obj = array();
				$obj['word'] = addslashes($word[0]);
				$obj['rword'] = addslashes($word[1]);
				
				$old = D('GoodsDictionary')->where("word = '$word[0]'")->find();
				if(isset($old['id']))
					D('GoodsDictionary')->where("id = ".intval($old['id']))->save($obj);
				else
					D('GoodsDictionary')->add($obj);
			}
		}
		
		$this->success (L('IMPORT_SUCCESS'));
	}
	
	public function export()
	{
		@set_time_limit(0);
		$word_file = 'GoodsDictionary.txt';
		$word_content = '';
		$words = D('GoodsDictionary')->where("status = 1")->select();
		foreach($words as $word)
		{
			$word_content .= $word['word']."=".$word['rword']."\n";
		}
		
		$word_content = trim($word_content);
		$time = gmtTime();

		header('Last-Modified: '.gmdate('D, d M Y H:i:s',$time).' GMT');
		header('Cache-control: no-cache');
		header('Content-Encoding: none');
		header('Content-Disposition: attachment; filename="'.$word_file.'"');
		header('Content-type: txt');
		header('Content-Length: '.strlen($word_content));
		echo $word_content;
		exit;
	}
	
	public function updateCache()
	{
		vendor("common");
		FS('Goods')->createDictionaryTable();
		$this->assign ('jumpUrl',U('GoodsDictionary/index'));
		$this->success (L('UPDATE_SUCCESS'));
	}
}
?>