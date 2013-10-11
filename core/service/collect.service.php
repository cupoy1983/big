<?php

require_once fimport('class/webcrawler');
class CollectService{
	
	const PARTNER_MOGUJIE = 1;
	const STATUS_INIT = 0;
	
	public function getClickURI($detailUrl){
		
		$options = array(
				//设置url
				CURLOPT_URL => $detailUrl,
				//设置header
				CURLOPT_HEADER => true,
				//不读取页面
				CURLOPT_NOBODY => true,
				//返回字符串
				CURLOPT_RETURNTRANSFER => true 
		);
		
		$webCrawler = WebCrawler::getInstance();
		$content = $webCrawler->getUrlContent($options, false);
		$headers = explode("\n", $content);
		$taobao = new TaobaoService();
		
		foreach($headers as $h){
			$h = explode(": ", $h);
			if($h[0] == "Location"){
				if(strpos($h[1], "s.click.taobao.com") !== false){
					$itemUrl = $taobao->getTaobaoURI(trim($h[1]), false);
					if(strpos($itemUrl, "s.click.taobao.com") !== false){
						$itemUrl = $taobao->getTaobaoURI(trim($h[1]), true);
					}
					return $itemUrl;
				}else if(strpos($h[1], "item.taobao.com") !== false || strpos($h[1], "detail.tmall.com") !== false){
					return $h[1];
				}else{
					//不能采集的商品
					return null;
				}
			}
		}
	}
	
	/**
	 * 将item(商品、图片、搭配)的信息入库，供采集使用
	 * @param $data
	 */
	public function saveItemInfo($data){
		//查询是否存在，不存在则insert
		$iid = FDB::resultFirst("SELECT id FROM ".FDB::table('item_collection')." WHERE out_item_id='".$data["out_item_id"]."' AND partner_id=".$data["partner_id"]);
		if(empty($iid)){
			FDB::insert('item_collection',$data);
		}
	}
	
	/**
	 * 将专辑的信息入库，供采集使用
	 * @param $data
	 */
	public function saveAlbumInfo($data){
		FDB::insert('album_collection',$data);
	}
	
	/**
	 * 淘宝宝贝采集(存入杂志社)
	 */
	public function taobaoCollect($url, $uid, $albumId){
		
		if(empty($url) || empty($url)){
			exit;
		}
		
		require fimport('service/sharegoods');
		$shareGoods = new SharegoodsService($url, $uid);
		$goods = $shareGoods->fetch($uid);
		$result = array();
		$result['success'] = false;
		//无该产品
		if(empty($result)){
			$result["status"] = 3;
			return $result;
		}
		if($goods["status"] == 1){
			$info = base64_encode(json_encode($goods));
			$key = $goods['item']['key'];
			$tags = implode(' ', FS('Words')->segment($goods['item']['name'], $_FANWE['setting']['share_tag_count']));
			
			$goods_sort[$key] = 1;
			$check = 1;
			
			$content = urldecode($content);
			if(empty($content)){
				$content = "推荐：" . $goods['item']['name'];
			}
			$_FANWE['request']['uid'] = $uid;
			$_FANWE['request']['goods'][$key] = $info;
			$_FANWE['request']['goods_sort'] = $goods_sort;
			$_FANWE['request']['tags'] = $tags;
			$_FANWE['request']['content'] = $content;
			$_FANWE['request']['albumid'] = $albumId;
			$_FANWE['request']['pub_out_check'] = $check;
			
			
			$file = $_SERVER['DOCUMENT_ROOT'] . '/services/module/share/save.php';
			$isReturn = true;
			if(file_exists($file)){
				include $file;
			}
		}elseif($goods["status"] == -1){
			$result["status"] = 4;
		}else{
			$result["status"] = 5;
		}
		//$result["status"]=0时，将状态设为其他错误$result["status"]=5
		if($result["status"] == 0){
			$result["status"] = 5;
		}
		return $result;
	}
	
	/**
	 * 查看该专辑是否已经被采集
	 * @param $uid
	 * @param $outId
	 * @param $partnerId
	 */
	public function getAlbumIdByOutId($uid, $outId, $partnerId){
		$sql = 'SELECT id FROM '.FDB::table('album_collection').' WHERE uid = '.$uid.' AND out_id='."'".$outId."'".' AND partner_id='.$partnerId;
		return FDB::resultFirst($sql);
	}
	
	/**
	 * 获取合作伙伴名
	 * @param $pid
	 * @return string
	 */
	public function getPartnerById($pid){
		if($pid == self::PARTNER_MOGUJIE){
			return "蘑菇街";
		}
	}
	
	public function getPartnerServiceById($pid){
		if($pid == self::PARTNER_MOGUJIE){
			return "Mogujie";
		}
	}
}

?>