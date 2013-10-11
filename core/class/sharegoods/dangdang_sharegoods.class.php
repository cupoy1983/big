<?php
class dangdang_sharegoods implements interface_sharegoods
{
	public function fetch($url, $uid)
	{
        global $_FANWE;

		$id = $this->getID($url);

		if(empty($id))
			return false;

		$key = 'dangdang_'.$id;
		
		$disable_goods = FDB::fetchFirst('SELECT * FROM '.FDB::table('goods_disable')." WHERE keyid = '$key'");
		if($disable_goods)
		{
			$result['status'] = -2;
			return $result;
		}
		
		$share_goods = FDB::fetchFirst('SELECT * FROM '.FDB::table('goods')." WHERE keyid = '$key'");
		if($share_goods && $_FANWE['uid'] > 0)
		{
			$user_goods = (int)FDB::fetchFirst('SELECT * FROM '.FDB::table('share_goods').' 
				WHERE uid = '.$_FANWE['uid'].' AND goods_id = '.$share_goods['id']);
			if($user_goods)
			{
				$result['status'] = -1;
				$result['share_id'] = $user_goods['share_id'];
				$result['goods_id'] = $user_goods['goods_id'];
				return $result;
			}
		}
		
		if(!$share_goods)
		{
			$result = getTempCache(md5($key));
			if($result === NULL)
			{
				//请求数据
				$content = getUrlContent("http://product.dangdang.com/product.aspx?product_id=".$id);
                                
				if(empty($content))
					return false;
		
				$pos = strpos($content,'</head>');
				$content = substr($content,$pos + 7);
				$content = preg_replace("/[\r\n]/",'',$content);
				$content = gbToUTF8($content);
        
				@preg_match("/<h1>(.*?)<span class=\"subtitle\">/",$content,$title);
				if(empty($title))
					return false;
				
				
                                @preg_match("/wsrc=\"(.*?)\"/", $content, $img);
                                if(empty($img))
                                        return false;
                      
				@preg_match("/<i class=\"red q_price\" id=\"promo_price\">&yen;(.*?)<\/i>/",$content,$price);
                                if(empty($price))
                                        return false;
				
				$result['image_server'] = '';
				if($_FANWE['setting']['share_image_save_type'] == 0)
				{
					$image['path'] = $img[1];
					$image['server_code'] = '';
				}
				elseif(!IS_UPYUN && FS("Image")->getIsServer())
				{
					$args = array();
					$args['pic_url'] = $img[1];
					$server = FS("Image")->formatServer($_FANWE['request']['image_server'],'DE');
					$server = FS("Image")->getImageUrlToken($args,$server,1);
					$body = FS("Image")->sendRequest($server,'savetemp',true);
					if(empty($body))
						return false;
					$image = unserialize($body);
					$result['image_server'] = $server['image_server'];
				}
				else
				{
					$image = copyFile($img[1],"temp",false);
					if($image === false)
						return false;
					$image['server_code'] = '';
				}
				$share_goods['img'] = $image['path'];
				
				$share_goods['id'] = 0;
				$share_goods['gid'] = $id;
				$share_goods['name'] = strip_tags(trim($title[1]));
				$share_goods['desc'] = '';
				$share_goods['cid'] = 0;
				$share_goods['price'] = (float)trim($price[1]);
				$share_goods['delist_time'] = 0;
				$share_goods['server_code'] = $image['server_code'];
				$share_goods['pic_url'] = $img[1];
				$share_goods['url'] = "http://product.dangdang.com/product.aspx?product_id=".$id;
				$from = $_FANWE['cache']['business']['dangdang']['from'];
				if(!empty($from))
					$share_goods['taoke_url'] = "http://union.dangdang.com/transfer.php?from=".$from."&ad_type=10&sys_id=1&backurl=".$share_goods['url'];
					
				$result['item'] = $share_goods;
				setTempCache(md5($key),$result);
			}
		}
		else
		{
			$share_goods['pic_url'] = getImgById($share_goods['img_id'],100,100);
			$result['item'] = $share_goods;
		}
		$result['status'] = 1;
		$result['item']['key'] = $key;
		$result['item']['type'] = "dangdang";
		return $result;
	}

	public function getID($url)
	{
		$id = 0;
		$parse = parse_url($url);
		if(isset($parse['query']))
		{
            parse_str($parse['query'],$params);
			if(isset($params['product_id']))
				$id = $params['product_id'];
            elseif(isset($params['id']))
                $id = $params['id'];
        }
		return $id;
	}

	public function getKey($url)
	{
		$id = $this->getID($url);
		return 'dangdang_'.$id;
	}
}
?>