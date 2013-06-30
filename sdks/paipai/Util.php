<?php
class Util {
	/**
	 * 解析xml
	 */
	static public function getXmlData ($strXml) {		
	
		//ADD BY ROGER。解决XML非法字符过滤	
		$strXml = preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$strXml);
		
		//全角转半角
		$strXml = SBC_DBC($strXml,1);		
		
		$pos = strpos($strXml, 'xml');
		if ($pos !== false) {
			$xmlCode=simplexml_load_string($strXml,'SimpleXMLElement', LIBXML_NOCDATA);
			$arrayCode=self::get_object_vars_final($xmlCode);
			return $arrayCode ;
		} else {
			return '';
		}
	}
	
	static private function get_object_vars_final($obj){
		if(is_object($obj)){
			$obj=get_object_vars($obj);
		}
			
		if(is_array($obj)){
			foreach ($obj as $key=>$value){
				$v = self::get_object_vars_final($value);
				//ADD BY ROGER。解决空值被转成空数组的问题
				if(is_array($v)&&(count($v)==0))$v=NULL;
				$obj[$key] = $v;
			}
		}
		return $obj;
	}


}

function SBC_DBC($str,$args2) { //半角和全角转换函数，第二个参数如果是0,则是半角到全角；如果是1，则是全角到半角
    $DBC = array( 
        '０' , '１' , '２' , '３' , '４' ,  
        '５' , '６' , '７' , '８' , '９' , 
        'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,  
        'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' , 
        'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,  
        'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' , 
        'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,  
        'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' , 
        'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,  
        'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' , 
        'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,  
        'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' , 
        'ｙ' , 'ｚ' , '－' , '　'  , '：' ,
  '．' , '，' , '／' , '％' , '＃' ,
  '！' , '＠' , '＆' , '（' , '）' ,
  '＜' , '＞' , '＂' , '＇' , '？' ,
  '［' , '］' , '｛' , '｝' , '＼' ,
  '｜' , '＋' , '＝' , '＿' , '＾' ,
  '￥' , '￣' , '｀'
    );
  $SBC = array( //半角
         '0', '1', '2', '3', '4',  
         '5', '6', '7', '8', '9', 
         'A', 'B', 'C', 'D', 'E',  
         'F', 'G', 'H', 'I', 'J', 
         'K', 'L', 'M', 'N', 'O',  
         'P', 'Q', 'R', 'S', 'T', 
         'U', 'V', 'W', 'X', 'Y',  
         'Z', 'a', 'b', 'c', 'd', 
         'e', 'f', 'g', 'h', 'i',  
         'j', 'k', 'l', 'm', 'n', 
         'o', 'p', 'q', 'r', 's',  
         't', 'u', 'v', 'w', 'x', 
         'y', 'z', '-', ' ', ':',
   '.', ',', '/', '%', '#',
   '!', '@', '&', '(', ')',
   '<', '>', '"', '\'','?',
   '[', ']', '{', '}', '\\',
   '|', '+', '=', '_', '^',
   '$', '~', '`'
    );
if($args2==0) 
   return str_replace($SBC,$DBC,$str);  //半角到全角
if($args2==1)
   return str_replace($DBC,$SBC,$str);  //全角到半角
else
   return false;
} 
?>