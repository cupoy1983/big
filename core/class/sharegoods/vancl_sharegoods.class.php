<?php
class vancl_sharegoods implements interface_sharegoods
{
	public function fetch($url, $uid)
	{
        global $_FANWE;
		
		if(strpos($url,'vjia.com') !== false)
		{
			return $this->vjiaFetch($url);
		}

		$id = $this->getID($url);
		if(empty($id))
			return false;

		$key = 'vancl_'.$id;
		
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
				$content = getUrlContent("http://item.vancl.com/".$id.".html");
				if(empty($content))
					return false;
		
				$pos = strpos($content,'</head>');
				$content = substr($content,$pos + 7);
				$content = preg_replace("/[\r\n]/",'',$content);
                                
				@preg_match("/<span id=\"styleinfo\".*?>(.*?)<\/span>/",$content,$title);
				if(empty($title))
					return false;
				@preg_match("/<img id=\"midimg\" src=\"(.*?)\"/",$content,$img);
                                if(empty($img))
					return false;
				@preg_match("/<div class=\"cuxiaoPrice \">.*?￥<strong>(.*?)<\/strong>/",$content,$price);
				if(empty($price))
					return false;
				
				$result['image_server'] = '';
				if($_FANWE['setting']['share_image_save_type'] == 0)
				{
					$image['path'] = str_replace('/small/','/big/',$img[1]);
					$image['server_code'] = '';
				}
				elseif(!IS_UPYUN && FS("Image")->getIsServer())
				{
					$args = array();
					$args['pic_url'] = str_replace('/small/','/big/',$img[1]);
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
					$image = copyFile(str_replace('/small/','/big/',$img[1]),"temp",false);
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
				$share_goods['pic_url'] = str_replace('/small/','/mid/',$img[1]);
				$share_goods['url'] = "http://item.vancl.com/".$id.".html";
				$Source = $_FANWE['cache']['business']['vancl']['Source'];
				if(!empty($Source))
					$share_goods['taoke_url'] = $share_goods['url']."?Source=".$Source;
					
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
		$result['item']['type'] = "vancl";
		return $result;
	}

	public function vjiaFetch($url)
	{
        global $_FANWE;
	
		$urls = $this->getVjiaID($url);
		if(empty($urls['id']))
			return false;

		$key = 'vjia_'.$urls['id'];
		$url = $urls['url'];
		
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
				$content = getUrlContent($url);
				if(empty($content))
					return false;
		
				$pos = strpos($content,'</head>');
				$content = substr($content,$pos + 7);
				$content = preg_replace("/[\r\n]/",'',$content);
				
				@preg_match("/<li class=\"title\">(.*?)<\/li>/",$content,$title);
				if(empty($title))
					return false;
		
				@preg_match("/<div class=\"sp-bigImg\">.*?<img.*?src=\"(.*?)\".*?\/>.*?<\/div>/",$content,$img);
				if(empty($img))
					return false;
				
				@preg_match("/<span id=\"SpecialPrice\">(.*?)<\/span>/",$content,$price);
				if(empty($price))
					return false;
				
				$result['image_server'] = '';
				if($_FANWE['setting']['share_image_save_type'] == 0)
				{
					$image['path'] = str_replace('/mid/','/big/',$img[1]);
					$image['server_code'] = '';
				}
				elseif(!IS_UPYUN && FS("Image")->getIsServer())
				{
					$args = array();
					$args['pic_url'] = str_replace('/mid/','/big/',$img[1]);
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
					$image = copyFile(str_replace('/mid/','/big/',$img[1]),"temp",false);
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
				$share_goods['url'] = $url;
				$Source = $_FANWE['cache']['business']['vancl']['Source'];
				if(!empty($Source))
					$share_goods['taoke_url'] = $share_goods['url']."?Source=".$Source;
					
				$result['item'] = $share_goods;
				setTempCache(md5($key),$result);
			}
		}
		else
		{
			$share_goods['pic_url'] = getImgById($share_goods['img_id'],100,100);
			$result['item'] = $share_goods;
		}
		
		$result['item']['key'] = $key;
		$result['item']['type'] = "vjia";
		return $result;
	}

	public function getID($url)
	{
		$id = '';
		$parse = parse_url($url);
		if(isset($parse['path']))
		{
			$parse = explode('/',$parse['path']);
			$parse = end($parse);
			$parse = explode('.',$parse);
			$id = current($parse);
        }
		return $id;
	}

	public function getVjiaID($url)
	{
		$id = '';
		$parse = parse_url($url);

		if(isset($parse['path']))
		{
			$url = $parse['scheme'].'://'.$parse['host'].$parse['path'];
			$parse = explode('/',$parse['path']);
			$parse = end($parse);
			$parse = explode('.',$parse);
			$id = current($parse);
			
        }

		return array('id'=>$id,'url'=>$url);
	}

	public function getKey($url)
	{
		$id = $this->getID($url);
		return 'vancl_'.$id;
	}
}
?>