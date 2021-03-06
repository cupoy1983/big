<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

/**  
 * words.service.php
 *
 * 单词服务
 *
 * @package service
 * @author awfigq <awfigq@qq.com>
 */
class WordsService
{
	/**  
	 * 文本分词
	 * @param string $text 需要分词的文本
	 * @param int $num 返回分词数量
	 * @return array
	 */
	public function segment($text,$num = 10)
	{
		$list = array();
		if(empty($text))
			return $list;
		
		//检测是否已安装php_scws扩展
		if(function_exists("scws_open"))
		{
			$sh = scws_open();
			scws_set_charset($sh,'utf8');
			scws_set_dict($sh,FANWE_ROOT.'public/scws/dict.utf8.xdb');
			scws_set_rule($sh,FANWE_ROOT.'public/scws/rules.utf8.ini');
			scws_set_ignore($sh,true);
			scws_send_text($sh, $text);
			$words = scws_get_tops($sh, $num);
			scws_close($sh);
		}
		else
		{
			require_once FANWE_ROOT.'public/pscws4/pscws4.class.php';
			$pscws = new PSCWS4();
			$pscws->set_dict(FANWE_ROOT.'public/scws/dict.utf8.xdb');
			$pscws->set_rule(FANWE_ROOT.'public/scws/rules.utf8.ini');
			$pscws->set_ignore(true);
			$pscws->send_text($text);
			$words = $pscws->get_tops($num);
			$pscws->close();
		}
		
		foreach($words as $word)
		{
			$list[] = $word['word'];
		}
		
		return $list;
	}
	
	public function segments($arr,$num = 10)
	{
		$list = array();
		if(empty($text))
			return $list;
		
		$words = array();
		
		//检测是否已安装php_scws扩展
		if(function_exists("scws_open"))
		{
			$sh = scws_open();
			scws_set_charset($sh,'utf8');
			scws_set_dict($sh,FANWE_ROOT.'public/scws/dict.utf8.xdb');
			scws_set_rule($sh,FANWE_ROOT.'public/scws/rules.utf8.ini');
			scws_set_ignore($sh,true);
			foreach($arr as $key => $text)
			{
				scws_send_text($sh, $text);
				$words[] = scws_get_tops($sh, $num);
			}
			scws_close($sh);
		}
		else
		{
			require_once FANWE_ROOT.'public/pscws4/pscws4.class.php';
			$pscws = new PSCWS4();
			$pscws->set_dict(FANWE_ROOT.'public/scws/dict.utf8.xdb');
			$pscws->set_rule(FANWE_ROOT.'public/scws/rules.utf8.ini');
			$pscws->set_ignore(true);
			foreach($arr as $key => $text)
			{
				$pscws->send_text($text);
				$words[] = $pscws->get_tops($num);
			}
			$pscws->close();
		}
		
		for($i = 0;$i < $num; $i++)
		{
			foreach($words as $item)
			{
				if(isset($item[$i]))
				{
					$word = $item[$i]['word'];
					if(isset($list[$word]))
						$list[$word]++;
					else
						$list[$word] = 1;
				}
			}
		}
		
		$list = array_slice($list,0,$num);
		return array_keys($list);
	}
	
	/**  
	 * 文本分词
	 * @param string $text 需要分词的文本
	 * @return array
	 */
	public function segmentAll($text) {
		$list = array ();
		if (empty ($text))
			return $list;

		//检测是否已安装php_scws扩展
		if (function_exists("scws_open")) {
			$sh = scws_open();
			scws_set_charset($sh, 'utf8');
			scws_set_dict($sh, FANWE_ROOT . 'public/scws/dict.utf8.xdb');
			scws_set_rule($sh, FANWE_ROOT . 'public/scws/rules.utf8.ini');
			scws_set_ignore($sh, true);
			scws_send_text($sh, $text);
			while ($words = scws_get_result($sh)) {
				foreach ($words as $word) {
					$list[] = $word['word'];
				}
			}
			scws_close($sh);
		} else {
			require_once FANWE_ROOT . 'public/pscws4/pscws4.class.php';
			$pscws = new PSCWS4();
			$pscws->set_dict(FANWE_ROOT . 'public/scws/dict.utf8.xdb');
			$pscws->set_rule(FANWE_ROOT . 'public/scws/rules.utf8.ini');
			$pscws->set_ignore(true);
			$pscws->send_text($text);
			while ($words = $pscws->get_result()) {
				foreach ($words as $word) {
					$list[] = $word['word'];
				}
			}
			$pscws->close();
		}

		return $list;
	}
	
	/**
	* utf8字符串分隔为unicode字符串
	* @param string $str 要转换的字符串
	* @param string $pre
	* @return string
	*/
	function segmentToUnicode($str, $pre = '') {
		$str = strtolower($str);
		$arr = array ();
		
		$temps = WordsService::segmentAll($str);
		foreach ($temps as $word) {
			$temp = $pre;
			$str_len = mb_strlen($word, 'UTF-8');
			for ($i = 0; $i < $str_len; $i++) {
				$s = mb_substr($word, $i, 1, 'UTF-8');
				if ($s != ' ' && $s != '　') {
					$temp .= 'ux' . utf8ToUnicode($s);
				}
			}
			$arr[] = $temp;
		}
		
		$str = clearSymbol($str);
		$str_len = mb_strlen($str, 'UTF-8');
		for ($i = 0; $i < $str_len; $i++) {
			$s = mb_substr($str, $i, 1, 'UTF-8');
			if ($s != ' ' && $s != '　') {
				$arr[] = $pre . 'ux' . utf8ToUnicode($s);
			}
		}
		
		$arr = array_unique($arr);
		return implode(' ', $arr);
	}
}
?>