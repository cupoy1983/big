<?php
// +----------------------------------------------------------------------
// | 方维购物分享网站系统 (Build on ThinkPHP)
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://fanwe.com All rights reserved.
// +----------------------------------------------------------------------

/**  
 * mogujie.service.php
 *
 * mogujie服务
 *
 * @package service
 * @author frankie
 */
require_once fimport('service/collect');
require_once thirdParty("phpQuery","phpQuery");

class MogujieService extends CollectService{
	
	
	/**
	 * 根据分页，遍历蘑菇街选款师专辑的信息并处理，为商品、图片的采集做前期准备：
	 * @param $url 选款师专辑当页url
	 */
	public function collectDarenAlbumInfo($url, $uid){
// 		$url = "http://www.mogujie.com/seerita/album";
// 		$url = "http://www.mogujie.com/u/1odd40/album";
		$webCrawler = WebCrawler::getInstance();
		$options = array(
			// 设置url
			CURLOPT_URL => $url,
			// 设置header
			CURLOPT_HEADER => false,
			// 返回字符串
			CURLOPT_RETURNTRANSFER => true 
		);
		$c = $webCrawler->getUrlContent($options, false);
		
		$doc = phpQuery::newDocument($c);
		unset($c);
		
		//遍历当页所有专辑，并收集各个专辑的信息并入库
		$index = 0;
		foreach(pq('.album_list .album_item') as $element){
			$albumUrl = pq($element)->find('.album_title')->attr("href");
			$outId = trim(str_replace("/album/show/", "", $albumUrl));
			$albumUrl = $webCrawler->reviseUrl($url, $albumUrl);
			MogujieService::collectAlbumInfo($albumUrl, $uid, $outId);
			$index ++;
		}
		//释放document内存
		$doc->unloadDocument();
		
		//index大于0，需要继续采集，否则终止采集
		if($index > 0){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 采集专辑信息，并入库，为采集该专辑中的所有产品做准备
	 * 根据用户名查询杂志社信息是否存在(新建、引用)
	 * @param $url 专辑url
	 * @param $uid 供采集的uid
	 * @param $identifier 杂志社外部标识符(用于判别是否需要新建杂志社)
	 *  
	 */
	public function collectAlbumInfo($url, $uid, $outId){
		//1.uid大于0，爬取专辑页面，并进行处理
		//2.否则跳过，不进行处理
		if($uid>0 && $outId!=false){
			//该专辑是否已采集
			$albumId = parent::getAlbumIdByOutId($uid, $outId, parent::PARTNER_MOGUJIE);
			if(empty($albumId)){
				$webCrawler = WebCrawler::getInstance();
				$options = array(
					// 设置url
					CURLOPT_URL => $url,
					// 设置header
					CURLOPT_HEADER => false,
					// 返回字符串
					CURLOPT_RETURNTRANSFER => true
				);
				$c = $webCrawler->getUrlContent($options, false);
				
				$doc = phpQuery::newDocument($c);
				unset($c);
				
				$info = pq('.album_info')->text();
				$title = pq('.album_nav_h1')->text();
				if($title == "默认专辑 "){
					$title = pq('.user_album_info .uname')->text() . "的杂志铺";
				}
				$data = array(
					'uid'        => $uid,
					'title'      => $title,
					'content'    => $info,
					'cid'        => 1,
					'show_type'  => 1,
				);
				//将该专辑采集入库
				$albumId = FS("Album")->collectAlbum($data);
				
				//将该专辑信息采集入库
				$data = array(
					'uid'        => $uid,
					'title'      => $title,
					'out_id'     => $outId,
					'partner_id' => parent::PARTNER_MOGUJIE,
					'url'        => trim($url),
					'album_id'   => $albumId,
					'gmt_create' => TIME_UTC,
					'gmt_modified' => TIME_UTC,
				);
				
				parent::saveAlbumInfo($data);
				
				//释放document内存
				$doc->unloadDocument();
			}
		}
	}
	
	/**
	 * 2.收集需要采集的宝贝链接并入库 返回true：有下一页 false:无下一页
	 * @param $url
	 * @param $uid
	 * @param $outId
	 */
	public function collectAlbumItemInfo($url, $uid, $albumId){
		$webCrawler = WebCrawler::getInstance();
		$options = array(
			// 设置url
			CURLOPT_URL => $url,
			// 设置header
			CURLOPT_HEADER => false,
			// 返回字符串
			CURLOPT_RETURNTRANSFER => true
		);
		$c = $webCrawler->getUrlContent($options, false);
		
		$doc = phpQuery::newDocument($c);
		unset($c);
		//2.专辑中ajax的产品信息采集
		$search = array('MOGUPROFILE = ','{ ',':',',',';');
		$replace = array('','{"','":',',"','');
		$data = pq('script:eq(1)')->text();
		$data = str_replace($search,$replace,$data);
		$data = json_decode(trim($data));
		
		//数据读取完毕释放document内存
		$doc->unloadDocument();
		
		//FIXME frankie 页码大于实际页码，查看是否起作用
		if(empty($data->{"bookajax"})){
			return false;
		}
		
		$ajaxUrl = $webCrawler->reviseUrl($url, $data->{"bookajax"});
		$counter = 0;
		$postData = array(
			"lastTweetId" => $data->{"lastTweetId"},
			"book" => $data->{"book"},
			"totalCol" => 4,
			"page" => $counter
		);
		while($counter >= 0){
			$webCrawler = WebCrawler::getInstance();
			$options = array(
				// 设置url
				CURLOPT_URL => $ajaxUrl,
				// 设置header
				CURLOPT_HEADER => false,
				//POST
				CURLOPT_POST => true,
				//POST DATA
				CURLOPT_POSTFIELDS => $postData,
				// 返回字符串
				CURLOPT_RETURNTRANSFER => true
			);
			$c = json_decode($webCrawler->getUrlContent($options, false));
			
			if(empty($c)){
				break;
			}
			$end = $c->{"result"}->{"data"}->{"is_end"};
			//没有更多条目则将计数器置0，否则进行下次操作
			if($end){
				$r = $counter;
				$counter = -1;
			}else{
				$postData["lastTweetId"] = $c->{"result"}->{"data"}->{"lastTweetId"};
				$postData["page"] = $counter ++;
			}
			foreach($c->{"result"}->{"html"}->{"book"} as $book){
				$subDoc = phpQuery::newDocument($book);
				$title = pq($subDoc)->find('.i_w_f .pic img')->attr("alt");
				$itemUrl = pq($subDoc)->find('.i_w_f .pic a:eq(0)')->attr("href");
				$outItemId = substr($itemUrl, 6, strpos($itemUrl, "?")-6);
				if(empty($itemUrl)){
					continue;
				}
				$itemUrl = $webCrawler->reviseUrl($url, $itemUrl);
		
				$d = array(
					"uid"         => $uid,
					"out_item_id" => $outItemId,
					"url"         => $itemUrl,
					"album_id"   => $albumId,
					"partner_id"   => parent::PARTNER_MOGUJIE,
					"status"      => parent::STATUS_INIT,
					"gmt_create"  => TIME_UTC,
				);
				parent::saveItemInfo($d);
		
				//释放document内存
				$subDoc->unloadDocument();
			}
		}
		
		//该分页遍历后，r不为空，则需下一分页采集，否则采集下一专辑
		//true:采集下一分页 
		//false：更新专辑内item_count并采集下一专辑
		if(empty($r)){
			//更新专辑总数
			$itemCount = M("ItemCollection")->where("album_id=".$albumId)->count();
			M("AlbumCollection")->where("album_id=".$albumId)->setField("item_count",$itemCount);
			return false;
		}else{
			return true;
		}
	}
	
	/**
	 * 采集蘑菇街选款师分享
	 * @param $url 选款师产品url
	 * 模式链接：
	 * 单品 http://www.mogujie.com/note/1hp6tku
	 * 搭配 http://www.mogujie.com/note/1i9bivu
	 * 图片 http://www.mogujie.com/note/1i42ffa
	 */
	public function publishShares($data){
		
		global $_FANWE;
		$url = $data["url"];
		$uid = $data["uid"];
		$albumId = $data["album_id"];
		
// 		$url = "http://www.mogujie.com/note/1ia1wxe?showtype=good&goodsid=1bf1nwk#content_top";
// 		$url = "http://www.mogujie.com/note/1iadyp2?showtype=image&imageid=156pf5c#content_top";
// 		$url = "http://www.mogujie.com/note/1ib0x32?showtype=image&imageid=1570d8s#content_top";
		
		$d = parse_url($url);
		$url = $d["scheme"]."://".$d["host"].$d["path"];

		$webCrawler = WebCrawler::getInstance();
		$options = array(
			// 设置url
			CURLOPT_URL => $url,
			// 设置header
			CURLOPT_HEADER => false,
			// 设置http header
			CURLOPT_HTTPHEADER =>array("User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:24.0) Gecko/20100101 Firefox/24.0" ),
			// 返回字符串
			CURLOPT_RETURNTRANSFER => true
		);
		$c = $webCrawler->getUrlContent($options, false);
		$doc = phpQuery::newDocument($c);
		unset($c);
		
		$goodsUrl = pq('#private_content .shop_link')->attr("href");
		
		if(!empty($goodsUrl)){
			$status = self::publishSingles($goodsUrl, $uid, $albumId);
		}else{
			pq(".share_content")->find('a')->remove();
			pq(".share_content")->find('span')->remove();
			$content = trim(pq(".share_content")->text());
			
			foreach(pq('#img_show_wrap .img_show img') as $key => $element){
				$imgs[$key] = $element->getAttribute("src");
			}
			
			foreach(pq('#note_info_show .goods_list li a') as $key => $element){
				$goods[$key] = $element->getAttribute("href");
			}
			$status = self::publishOthers($imgs, $goods, $content, $uid, $albumId);
		}
		$doc->unloadDocument();
		
		//修改item_collection的状态
		FDB::query("UPDATE ".FDB::table('item_collection')." SET status=".$status." WHERE id=".$data["id"]);
		
		return $status;
	}
	
	/**
	 * 发表单品
	 * @param $url
	 */
	private function publishSingles($url, $uid, $albumId){
		$result = self::getTaobaoItemUrl($url);
		if(!empty($result["itemUrl"])){
			$result = parent::taobaoCollect($result["itemUrl"], $uid, $albumId);
		}
		return $result["status"];
	}
	
	/**
	 * 发表搭配、晒货、图片
	 * @param $imgs
	 * @param $goods
	 * $param $content
	 */
	private function publishOthers($imgs, $goods, $content, $uid, $albumId){
		
		$urls = array();
		//遍历获取淘宝url，并存入数组
		foreach($goods as $k => $v){
			$url = self::getTaobaoItemUrl($v);
			if(!empty($url["itemUrl"])){
				$urls[$k] = $url["itemUrl"];
			}
		}
		
		if(empty($urls) && empty($imgs)){
			return 8;
		}else{
			$result = parent::collocationCollect($urls, $imgs, $content, $uid, $albumId);
		}
		return $result["status"];
		
	}
	
	private function getTaobaoItemUrl($url){
		$webCrawler = WebCrawler::getInstance();
		$options = array(
				// 设置url
				CURLOPT_URL => $url,
				// 设置header
				CURLOPT_HEADER => false,
				// 设置http header
				CURLOPT_HTTPHEADER =>array("User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:24.0) Gecko/20100101 Firefox/24.0" ),
				// 返回字符串
				CURLOPT_RETURNTRANSFER => true
		);
		
		$c = $webCrawler->getUrlContent($options, false);
		if($c){
			$arr=explode("'",$c);
			//数组长度大于3，说明该url经过js加密，标记状态为6
			if(count($arr) > 3){
				$result["status"] = 6;
			}else{
				$itemUrl = $arr[1];
			}
		}else{
			$options[CURLOPT_HEADER] = true;
			$c = $webCrawler->getUrlContent($options, false);
				
			$headers = explode("\n", $c);
			foreach($headers as $h){
				$h = explode(": ", $h);
				if($h[0]=="Location"){
					$u = explode("link=", $h[1]);
					$itemUrl = $u[1];
				}
			}
			if(empty($u[1])){
				//paipai's goods status = 5
				$result["status"] = 5;
			}
		}
		
		if(!empty($itemUrl)){
			$result["itemUrl"] = trim($itemUrl);
		}
		return $result;
	}
	
}