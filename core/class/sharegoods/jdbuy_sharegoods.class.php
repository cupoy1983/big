<?php
class jdbuy_sharegoods implements interface_sharegoods
{
	public function fetch($url, $uid)
	{
        global $_FANWE;
		$id = $this->getID($url);

		if(empty($id))
			return false;

		$key = '360buy_'.$id;

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
				$content = getUrlContent("http://item.jd.com/743579.html");

				if(empty($content))
					return false;
		
				$pos = strpos($content,'</head>');
				$content = substr($content,$pos + 7);
                                
				$content = preg_replace("/[\r\n]/",'',$content);
				$content = gbToUTF8($content);
                                
				@preg_match("/<h1>(.*?)<\/h1>/",$content,$title);

				if(empty($title))
					return false;
                                
				@preg_match("/<div id=\"preview\".*?>.*?<img.*?src=\"(.*?)\".*?jqimg=\"(.*?)\"\/>.*?<\/div>/",$content,$img);
				if(empty($img))
					return false;
                                
				$price_data = file_get_contents('http://p.3.cn/prices/get?skuid=J_'.$id.'&type=1');
                                $price_arr = json_decode($price_data);
                                $price = $price_arr[0]->p;
                                
				if(empty($price))
					return false;
                                
				$img[1] = trim($img[1]);
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
				$share_goods['price'] = (float)$price;
				$share_goods['delist_time'] = 0;
				$share_goods['server_code'] = $image['server_code'];
				$share_goods['pic_url'] = $img[1];
				$share_goods['url'] = "http://item.jd.com/".$id.".html";
				$unionId = $_FANWE['cache']['business']['jdbuy']['unionId'];
				if(!empty($unionId))
					$share_goods['taoke_url'] = "http://click.union.jd.com/JdClick/?unionId=".$unionId."&t=4&to=".$share_goods['url'];
					
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
		$result['item']['type'] = "360buy";
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

	public function getKey($url)
	{
		$id = $this->getID($url);
		return '360buy_'.$id;
	}

	public function getPrice($img,$url)
	{
		$dir = './public/upload/temp/'.fToDate(NULL,'Y/m/d/H');
		$file_name = md5(microtime(true)).random('6').'.png';
		makeDir(FANWE_ROOT.$dir);
		$file_path = FANWE_ROOT.$dir."/".$file_name;
		
		$img_data = getUrlContent($img,true,$url);
		if(empty($img_data) || @file_put_contents($file_path,$img_data) == 0)
			return false;

		$xs_count = 2;
		$keys = array(
			'001111100011000110110000011110000011110000011110000011110000011110000011110000011011000110001111100'=>0,
			'000011000001111000000011000000011000000011000000011000000011000000011000000011000000011000001111110'=>1,
			'011111100110000110110000011000000011000000110000001100000011000000110000001100000011000000111111111'=>2,
			'011111100110000110110000011000000011000000110000111100000000110000000011110000011110000110011111100'=>3,
			'000000110000001110000010110000100110001000110010000110100000110111111111000000110000000110000000110'=>4,
			'011111111011000000011000000011000000011111100000000110000000011000000011110000011110000110011111100'=>5,
			'000111110001100000011000000110000000110111100111000110110000011110000011110000011011000110001111100'=>6,
			'111111111000000011000000110000000110000001100000001100000011000000011000000110000000110000001100000'=>7,
			'001111100011000110110000011110000011011000110001111100011000110110000011110000011011000110001111100'=>8,
			'001111100011000110110000011110000011110000011011000111001111011000000011000000110000001100011111000'=>9,
		);

		$config = array(
			'word_width'=>9,
			'offset_l'=>16,
			'offset_t'=>5,
			'word_spacing'=>2,
			'd_width'=>5,
		);

		$res = imagecreatefrompng($file_path);
		$size = getimagesize($file_path);
		$data = array();

		$offset_r = 0;
		for($j = $size[0] - 1; $j >= 0; $j--)
		{	
			$rgb = imagecolorat($res,$j,7);
			$rgbarray = imagecolorsforindex($res, $rgb);
			if($rgbarray['red'] < 125 || $rgbarray['green']<125 || $rgbarray['blue'] < 125)
				break;
			else
				$offset_r++;
		}
		
		$offset_b = 0;
		for($i= $size[1] - 1; $i >= 0; $i--)
		{
			$rgb = imagecolorat($res,$size[0] - $offset_r - 3,$i);
			$rgbarray = imagecolorsforindex($res, $rgb);
			if($rgbarray['red'] < 125 || $rgbarray['green']<125 || $rgbarray['blue'] < 125)
				break;
			else
				$offset_b++;
		}
		
		$word_width = $config['word_width'];
		$offset_l = $config['offset_l'];
		$offset_t = $config['offset_t'];
		$word_spacing = $config['word_spacing'];
		$d_width = $config['d_width'];

		$no_l = $size[0] - ($xs_count * ($word_width + $word_spacing) + $d_width + $offset_r);
		$no_r = $no_l + $d_width;

		for($i=$offset_t; $i < ($size[1] - $offset_b); $i++)
		{
			$w = 0;
			for($j=$offset_l; $j < ($size[0] - $offset_r); $j++)
			{
				if($j >= $no_l && $j < $no_r)
					continue;
				
				$rgb = imagecolorat($res,$j,$i);
				$rgbarray = imagecolorsforindex($res, $rgb);
				if($rgbarray['red'] < 125 || $rgbarray['green']<125 || $rgbarray['blue'] < 125)
					$data[$i - $offset_t][$w]=1;
				else
					$data[$i - $offset_t][$w]=0;
				$w++;
			}
		}

		$prices = array();
		$length = count($data[0]);
		$height = count($data);
		$count = ($length + $word_spacing) / ($word_width + $word_spacing);
		for($i = 0;$i < $count;$i++)
		{
			$x = $i * ($word_width + $word_spacing);
			for($h = 0;$h < $height;$h++) 
			{
				$temp = '';
				for($w = $x; $w < ($x + $word_width);$w++)
				{
					$prices[$i].= $data[$h][$w];
				}
			}
		}
		
		$number = '';
		foreach($prices as $price)
		{
			$number .= $keys[$price];
		}

		@unlink($file_path);
		return ((float)$number) / 100;
	}
}
?>