<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;

class TcsQcloudApi extends Controller{
	
	protected $HttpUrl = "yunsou.api.qcloud.com";
	protected $HttpMethod = "GET";
	protected $isHttps = true;
	
	protected $secretKey;
	protected $secretId;
	protected $appId;
	
	const SECRETKEY = 'DEC2hJk4B622r9QiokV7YoskQuDNPL8s';
	const SECRETID	= 'AKIDSoqmX0Wk282oPswIH5hicT8br7DEDg7N';
	const APPID = '58260002';
	const REGION = 'bj';
	
	public function __construct($appId = self::APPID, $secretId = self::SECRETID, $secretKey = self::SECRETKEY){
		
		$this->appId = $appId;
		$this->secretId = $secretId;
		$this->secretKey = $secretKey;
	}
	public function yunsouDataManipulation(){
		$ret = [
			'r' => 0,
			'msg' => '操作成功',
		];
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		if( !($tag_id > 0 && $themeid > 0) ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
			return json_encode( $ret );
			exit;
		}
		$op_type = (input('op_type'))?input('op_type'):'add';//default add
		
		$tag = model('Tag');
		$COMMON_PARAMS = array(
	        'Nonce'=> rand(),
	        'Timestamp'=>time(NULL),
	        'Action'=> 'DataManipulation',
	        'SecretId'=> $this->secretId,
	        'Region' => self::REGION,
	        'op_type' => $op_type,
	        'appId' => $this->appId,
		);
		$content = $tag->get_tag_by_themeid();
		$PRIVATE_PARAMS = [];
		foreach($content as $k => $v){
			$PRIVATE_PARAMS['contents.'.$k.'.name'] = $v['name'];
			$PRIVATE_PARAMS['contents.'.$k.'.tagid'] = $v['tagid'];
			$PRIVATE_PARAMS['contents.'.$k.'.themeid'] = $v['themeid'];
		}
		$res = $this->CreateRequest($COMMON_PARAMS, $PRIVATE_PARAMS);
		
		$ret['r'] = $res['retcode'];
		$ret['msg'] = $res['errmsg'];
		return json_encode($ret);
	}
	
	public function yunsouDataSearch(){
		$ret = [
			'r' => 0,
			'msg' => '操作成功',
			'data' => [],
		];
		$search_query = input('search_query');
		if( $search_query == ''){
			$ret['r'] = -1;
			$ret['msg'] = '查询内容不能为空';
			return json_encode( $ret );
			exit;
		}
		$COMMON_PARAMS = array(
	        'Nonce' => rand(),
	        'Timestamp' => time(NULL),
	        'Action' => 'DataSearch',
	        'SecretId'=> $this->secretId,
	        'Region' => self::REGION,
	        'search_query' => $search_query,
	        'page_id' => 0,
	        'num_per_page' => 10,
	        'appId' =>  $this->appId,
		);
		$PRIVATE_PARAMS = [];
		
		$res = $this->CreateRequest( $COMMON_PARAMS, $PRIVATE_PARAMS);
		$ret['r'] = $res['code'];
		$ret['msg'] = $res['message'];
		$ret['data'] = $res['data'];
		
		return json_encode( $ret );
	}
	
	
	public function CreateRequest( $COMMON_PARAMS, $PRIVATE_PARAMS)
	{
	    $FullHttpUrl = $this->HttpUrl."/v2/index.php";
	
	    /***************对请求参数 按参数名 做字典序升序排列，注意此排序区分大小写*************/
	    $ReqParaArray = array_merge($COMMON_PARAMS, $PRIVATE_PARAMS);
	    ksort($ReqParaArray);
	
	    $SigTxt = $this->HttpMethod.$FullHttpUrl."?";
	
	    $isFirst = true;
	    foreach ($ReqParaArray as $key => $value)
	    {
	        if (!$isFirst) 
	        { 
	            $SigTxt = $SigTxt."&";
	        }
	        $isFirst= false;
	        /*拼接签名原文时，如果参数名称中携带_，需要替换成.*/
	        if(strpos($key, '_'))
	        {
	            $key = str_replace('_', '.', $key);
	        }
	        $SigTxt = $SigTxt.$key."=".$value;
	    }
	    /*********************根据签名原文字符串 $SigTxt，生成签名 Signature******************/
	    $Signature = base64_encode(hash_hmac('sha1', $SigTxt, $this->secretKey, true));
	    /***************拼接请求串,对于请求参数及签名，需要进行urlencode编码********************/
	    $Req = "Signature=".urlencode($Signature);
	    foreach ($ReqParaArray as $key => $value)
	    {
	        $Req=$Req."&".$key."=".urlencode($value);
	    }
	    /*********************************发送请求********************************/
	    if($this->HttpMethod === 'GET')
	    {
	        if($this->isHttps === true)
	        {
	            $Req="https://".$FullHttpUrl."?".$Req;
	        }
	        else
	        {
	            $Req="http://".$FullHttpUrl."?".$Req;
	        }
	        $Rsp = file_get_contents($Req);
	    }
	    else
	    {
	        if($this->isHttps === true)
	        {
	            $Rsp= $this->SendPost("https://".$FullHttpUrl,$Req);
	        }
	        else
	        {
	            $Rsp= $this->SendPost("http://".$FullHttpUrl,$Req);
	        }
	    }
		return json_decode( $Rsp, true);
	}

	public function SendPost($FullHttpUrl,$Req){
	
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Req);
        curl_setopt($ch, CURLOPT_URL, $FullHttpUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->isHttps === true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
        }
        $result = curl_exec($ch);
        
        return $result;
	}
	
}






?>