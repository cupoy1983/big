<?php
require_once FANWE_ROOT.'sdks/paipai/PaiPaiOpenApiOauth.php';
require_once FANWE_ROOT.'sdks/paipai/Util.php';
class paipai_sharegoods implements interface_sharegoods
{
	public function fetch($url)
	{
        global $_FANWE;
		
		//QQ号
		define('PAIPAI_API_UIN',trim($_FANWE['cache']['business']['paipai']['uin']));
		//令牌
		define('PAIPAI_API_APPOAUTHID',trim($_FANWE['cache']['business']['paipai']['appoauthid']));
		//APP_KEY
		define('PAIPAI_API_APPOAUTHKEY',trim($_FANWE['cache']['business']['paipai']['appoauthkey']));
		define('PAIPAI_API_ACCESSTOKEN',trim($_FANWE['cache']['business']['paipai']['accesstoken']));
		define('PAIPAI_API_USERID',trim($_FANWE['cache']['business']['paipai']['userid']));

		$id = $this->getID($url);

		if(empty($id))
			return false;

		$key = 'paipai_'.$id;
		
		$disable_goods = FDB::fetchFirst('SELECT * FROM '.FDB::table('goods_disable')." WHERE keyid = '$key'");
		if($disable_goods)
		{
			$result['status'] = -2;
			return $result;
		}
		
		$share_goods = FDB::fetchFirst('SELECT * FROM '.FDB::table('goods')." WHERE keyid = '$key'");
		if($share_goods)
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
				$sdk = new PaiPaiOpenApiOauth(PAIPAI_API_APPOAUTHID,PAIPAI_API_APPOAUTHKEY,PAIPAI_API_ACCESSTOKEN,PAIPAI_API_UIN);
				$sdk->setApiPath("/item/getItem.xhtml");
				$sdk->setMethod("get");
				$sdk->setCharset("utf-8");
				
				$params = &$sdk->getParams();
				$params["itemCode"] = $id;
				$params["needDetailInfo"] = 1;
				
				$goods = $sdk->invoke();
				$goods = Util::getXmlData($goods);
				
				if($goods['errorCode'] > 0)
					return false;
		
				if(empty($goods['picLink']))
					return false;
					
				if(!FS("Goods")->getIsDisableByCid('paipai',$goods['classId']))
				{
					$result['status'] = -4;
					return $result;
				}
					
				if(!empty($goods['sellerUin']) && !empty($goods['sellerName']))
				{
					$result['shop']['name'] = $goods['sellerName'];
					$result['shop']['shop_id'] = $goods['sellerUin'];
					$result['shop']['url'] = 'http://shop.paipai.com/'.$goods['sellerUin'];

					$disable_shop = FDB::fetchFirst('SELECT * FROM '.FDB::table('shop_disable')." WHERE shop_url = '".$result['shop']['url']."'");
					if($disable_shop)
					{
						$result['status'] = -3;
						return $result;
					}
					
					$shop_id = FS("Shop")->getShopIdByUrl($result['shop']['url']);
					if($shop_id > 0)
					{
						$share_goods['shop_id'] = $shop_id;
						unset($result['shop']);
					}
				}
				
				$result['image_server'] = '';
				if($_FANWE['setting']['share_image_save_type'] == 0)
				{
					$image['path'] = $goods['picLink'];
					$image['server_code'] = '';
				}
				elseif(!IS_UPYUN && FS("Image")->getIsServer())
				{
					$args = array();
					$args['pic_url'] = $goods['picLink'];
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
					$image = copyFile($goods['picLink'],"temp",false);
					if($image === false)
						return false;
					$image['server_code'] = '';
				}
				$share_goods['img'] = $image['path'];
				
				$share_goods['id'] = 0;
				$share_goods['gid'] = $id;
				$share_goods['name'] = $goods['itemName'];
				$share_goods['desc'] = htmlspecialchars_decode($goods['detailInfo']);
				$share_goods['cid'] = FS("Goods")->getCid('paipai',$goods['classId']);
				$share_goods['price'] = $goods['itemPrice'] / 100;
				$share_goods['delist_time'] = (int)str2Time($goods['lastToSaleTime']) + (int)$goods['validDuration'];
				$share_goods['server_code'] = $image['server_code'];
				$share_goods['pic_url'] = $goods['picLink'];
				$share_goods['url'] = 'http://auction1.paipai.com/'.$goods['itemCode'];
				
				$sdk = new PaiPaiOpenApiOauth(PAIPAI_API_APPOAUTHID,PAIPAI_API_APPOAUTHKEY,PAIPAI_API_ACCESSTOKEN,PAIPAI_API_UIN);
				$sdk->setApiPath("/cps/constructCpsUrl.xhtml");
				$sdk->setMethod("get");
				$sdk->setCharset("utf-8");
				
				$params = &$sdk->getParams();
				$params["userId"] = PAIPAI_API_USERID;
				$params["urlType"] = 2;
				$params["itemCodes"] = $id;
				
				$cps = $sdk->invoke();
				$cps = Util::getXmlData($cps);
				if($cps && $goods['errorCode'] == 0 && count($cps['urlList']) > 0)
				{
					$share_goods['taoke_url'] = current($cps['urlList']);
				}
				
				$result['item'] = $share_goods;
				setTempCache(md5($key),$result);
			}
		}
		else
		{
			$share_goods['pic_url'] = getImgById($share_goods['img_id'],100,100);
			if($share_goods['cid'] == 0)
			{
				$sdk = new PaiPaiOpenApiOauth(PAIPAI_API_APPOAUTHID,PAIPAI_API_APPOAUTHKEY,PAIPAI_API_ACCESSTOKEN,PAIPAI_API_UIN);
				$sdk->setApiPath("/item/getItem.xhtml");
				$sdk->setMethod("get");
				$sdk->setCharset("utf-8");
				
				$params = &$sdk->getParams();
				$params["itemCode"] = $id;
				
				$goods = $sdk->invoke();
				$goods = Util::getXmlData($goods);
				if($goods['errorCode'] == 0 && !empty($goods['classId']))
				{
					$update_goods = array();
					$share_goods['cid'] = $update_goods['cid'] = FS("Goods")->getCid('paipai',$goods['classId']);
					$share_goods['delist_time'] = $update_goods['delist_time'] = (int)str2Time($goods['lastToSaleTime']) + (int)$goods['validDuration'];
					FDB::update('goods',$update_goods,'id = '.$share_goods['id']);
				}
			}
			$result['item'] = $share_goods;
		}
		$result['status'] = 1;
		$result['item']['key'] = $key;
		$result['item']['type'] = "paipai";
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
			$parse = explode('-',$parse);
			$id = current($parse);
        }
		return $id;
	}

	public function getKey($url)
	{
		$id = $this->getID($url);
		return 'paipai_'.$id;
	}
}
?>