<?php

require fimport('class/webcrawler');
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
	 * 查看该专辑是否已经被采集
	 * @param $uid
	 * @param $outId
	 * @param $partnerId
	 */
	public function getAlbumIdByOutId($uid, $outId, $partnerId){
		$sql = 'SELECT id FROM '.FDB::table('album_collection').' WHERE uid = '.$uid.' AND out_id='."'".$outId."'".' AND partner_id='.$partnerId;
		return FDB::resultFirst($sql);
	}
}

?>