<?php
class ShouModule
{
	public function index()
	{
		global $_FANWE;
		$href = $_FANWE['request']['href'];
		if(empty($href))
			exit;
		
		$href = urldecode($href);
		if($_FANWE['uid'] > 0)
		{
			$type = $_FANWE['request']['type'];
			if(empty($type))
				exit;
			
			if($type == 'g')
			{
				require fimport('service/sharegoods');
				$share_module = new SharegoodsService($href);
				$goods = $share_module->fetch();
				if($goods)
					$goods = base64_encode(json_encode($goods));
				else
					exit;
			}
			else
			{
				$width = (int)$_FANWE['request']['w'];
				$height = (int)$_FANWE['request']['h'];
				if($width == 0 || $height == 0)
					exit;
				
				$photo = array();
				$photo['error_code'] = 0;
				$photo['status'] = 0;
				
				$bln = true;
				
				if($_FANWE['setting']['share_image_save_type'] == 1)
				{
					$size = getImageLength($href);
					if($size == 0)
					{
						$photo['status'] = -1;
						$bln = false;
					}
					elseif($size > $_FANWE['setting']['max_upload'])
					{
						$photo['status'] = -2;
						$bln = false;
					}
				}
				
				
				if($_FANWE['setting']['share_image_save_type'] == 1)
				{
					if($bln)
					{
						$img = getTempCache(md5($href));
						if($img === NULL)
						{
							if(!IS_UPYUN && FS("Image")->getIsServer())
							{
								$args = array();
								$args['pic_url'] = $href;
								$server = FS("Image")->getServer();
								$server = FS("Image")->getImageUrlToken($args,$server,1);
								$body = FS("Image")->sendRequest($server,'savetemp',true);
								if(empty($body))
									exit;
								$img = unserialize($body);
								$photo['image_server'] = $server['image_server'];
							}
							else
							{
								$img = copyFile($href,"temp",false);
								if($img === false)
									exit;
								$img['server_code'] = '';
							}
						}
						
						if($img)
						{
							setTempCache(md5($href),$img);
							$photo['status'] = 1;
							$photo['img'] = $img['url'];
							$info = array('path'=>$img['path'],'url'=>$img['url'],'type'=>'default','server_code'=>$img['server_code'],'image_width'=>$img['width'],'image_height'=>$img['height']);
							$photo['info'] = base64_encode(json_encode($info));
						}
					}
				}
				else
				{
					$photo['img'] = $href;
					$photo['status'] = 1;
					$info = array('path'=>$href,'url'=>$href,'type'=>'default','server_code'=>'','image_width'=>$width,'image_height'=>$height);
					$photo['info'] = base64_encode(json_encode($info));
				}
			}
		}
		
		include template('page/shou_index');
		display();
	}
	
	public function help()
	{
		global $_FANWE;
		include template('page/shou_help');
		display();	
	}
}
?>