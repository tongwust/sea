<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Cookie;

class TcsQcloudApi extends Controller{
	
	protected $HttpUrl = "cvm.api.qcloud.com";
	protected $HttpMethod="GET";
	protected $isHttps =true;
	
	protected $secretKey;
	protected $secretId;
	
	const SECRETKEY = 'DEC2hJk4B622r9QiokV7YoskQuDNPL8s';
	const SECRETID	= 'AKIDSoqmX0Wk282oPswIH5hicT8br7DEDg7N';
	
	public function __construct($secretId = SECRETID, $secretKey = SECRETKEY){
		
		$this->secretId = $secretId;
		$this->secretKey = $secretKey;
		
	}
	/*下面这五个参数为所有接口的 公共参数；对于某些接口没有地域概念，则不用传递Region（如DescribeDeals）*/
	$COMMON_PARAMS = array(
	        'Nonce'=> rand(),
	        'Timestamp'=>time(NULL),
	        'Action'=>'DescribeInstances',
	        'SecretId'=> 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
	        'Region' =>'bj',
	);
	
	/*下面这两个参数为 DescribeInstances 接口的私有参数，用于查询特定的虚拟机列表*/
	$PRIVATE_PARAMS = array(
	        'instanceIds.0'=> 'qcvm00001',
	        'instanceIds.1'=> 'qcvm00002',
	);
	
	
	
	
}






?>