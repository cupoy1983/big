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
 内容锚文本管理
 +------------------------------------------------------------------------------
 */
class AnchorAction extends CommonAction
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
			FROM '.C("DB_PREFIX").'anchor '.$where;

		$count = $model->query($sql);
		$count = $count[0]['wcount'];

		$sql = 'SELECT * FROM '.C("DB_PREFIX").'anchor '.$where;
		$this->_sqlList($model,$sql,$count,$parameter,'id');
		
		$this->display ();
	}
	
	public function import()
	{
		$this->display();
	}
	
	public function save()
	{
		@set_time_limit(0);
		$source = FANWE_ROOT."public/upload/temp/anchor.txt";
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
				$word = explode("\t",$word);
				$obj = array();
				$obj['word'] = addslashes($word[0]);
				$obj['url'] = addslashes($word[1]);
				$obj['target'] = (int)$word[2] > 0 ? '_self' : '_blank';
				$obj['brief'] = addslashes($word[3]);
				
				$old = D('Anchor')->where("word = '$obj[word]'")->find();
				if(isset($old['id']))
					D('Anchor')->where("id = ".intval($old['id']))->save($obj);
				else
					D('Anchor')->add($obj);
			}
		}
		
		$this->success (L('IMPORT_SUCCESS'));
	}
	
	public function export()
	{
		@set_time_limit(0);
		$word_file = 'anchor.txt';
		$word_content = '';
		$words = D('Anchor')->where("status = 1")->select();
		foreach($words as $word)
		{
			$word_content .= $word['word']."\t".$word['url']."\t".($word['target'] == '_blank' ? 0 : 1)."\t".$word['brief']."\n";
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
		createAnchorTable();
		$this->assign ('jumpUrl',U('Anchor/index'));
		$this->success (L('UPDATE_SUCCESS'));
	}
}

function getTargetName($target)
{
	return L("TARGET".$target);
}
?>