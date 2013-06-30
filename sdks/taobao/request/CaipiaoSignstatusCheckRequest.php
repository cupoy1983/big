<?php
/**
 * TOP API: taobao.caipiao.signstatus.check request
 * 
 * @author auto create
 * @since 1.0, 2012-08-16 16:40:52
 */
class CaipiaoSignstatusCheckRequest
{
	/** 
	 * 用户的淘宝数字ID
	 **/
	private $userNumId;
	
	private $apiParas = array();
	
	public function setUserNumId($userNumId)
	{
		$this->userNumId = $userNumId;
		$this->apiParas["user_num_id"] = $userNumId;
	}

	public function getUserNumId()
	{
		return $this->userNumId;
	}

	public function getApiMethodName()
	{
		return "taobao.caipiao.signstatus.check";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->userNumId,"userNumId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
