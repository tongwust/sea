<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Loader;

use QcloudApi\QcloudApi;

class Tag extends Controller{
	
	public function index(){
		
	}
	
	public function test(){
		
		$config = array('SecretId'  => 'AKIDSoqmX0Wk282oPswIH5hicT8br7DEDg7N',
                'SecretKey'      => 'DEC2hJk4B622r9QiokV7YoskQuDNPL8s',
                'RequestMethod'  => 'GET',
                'DefaultRegion'  => 'bj');
		Loader::import('QcloudApi\QcloudApi', EXTEND_PATH);
		$cvm = QcloudApi::load(QcloudApi::MODULE_CVM, $config);
		
		$package = array('offset' => 0, 'limit' => 3, 'SignatureMethod' =>'HmacSHA256');
		
		$a = $cvm->DescribeInstances($package);
		// $a = $cvm->generateUrl('DescribeInstances', $package);
		
		if ($a === false) {
		    $error = $cvm->getError();
		    echo "Error code:" . $error->getCode() . ".\n";
		    echo "message:" . $error->getMessage() . ".\n";
		    echo "ext:" . var_export($error->getExt(), true) . ".\n";
		} else {
		    var_dump($a);
		}
		
		echo "\nRequest :" . $cvm->getLastRequest();
		echo "\nResponse :" . $cvm->getLastResponse();
		echo "\n";
	}
	
	public function t(){
		
		$HttpUrl="cvm.api.qcloud.com";
		$HttpMethod="GET"; 
		$isHttps =true;
		$secretKey='DEC2hJk4B622r9QiokV7YoskQuDNPL8s';
		
		$COMMON_PARAMS = array(
	        'Nonce'=> rand(),
	        'Timestamp'=>time(NULL),
	        'Action'=>'DescribeInstances',
	        'SecretId'=> 'AKIDSoqmX0Wk282oPswIH5hicT8br7DEDg7N',
	        'Region' =>'gz',
		);
		
		$PRIVATE_PARAMS = array(
       		'instanceIds.0'=> 'qcvm00001',
        	'instanceIds.1'=> 'qcvm00002',
		);
		
		$this->CreateRequest($HttpUrl,$HttpMethod,$COMMON_PARAMS,$secretKey, $PRIVATE_PARAMS, $isHttps);
	}
	
	function CreateRequest($HttpUrl,$HttpMethod,$COMMON_PARAMS,$secretKey, $PRIVATE_PARAMS, $isHttps)
{
    $FullHttpUrl = $HttpUrl."/v2/index.php";

    /***************对请求参数 按参数名 做字典序升序排列，注意此排序区分大小写*************/
    $ReqParaArray = array_merge($COMMON_PARAMS, $PRIVATE_PARAMS);
    ksort($ReqParaArray);

    /**********************************生成签名原文**********************************
     * 将 请求方法, URI地址,及排序好的请求参数  按照下面格式  拼接在一起, 生成签名原文，此请求中的原文为 
     * GETcvm.api.qcloud.com/v2/index.php?Action=DescribeInstances&Nonce=345122&Region=gz
     * &SecretId=AKIDz8krbsJ5yKBZQ    ·1pn74WFkmLPx3gnPhESA&Timestamp=1408704141
     * &instanceIds.0=qcvm12345&instanceIds.1=qcvm56789
     * ****************************************************************************/
    $SigTxt = $HttpMethod.$FullHttpUrl."?";

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

        $SigTxt=$SigTxt.$key."=".$value;
    }

    /*********************根据签名原文字符串 $SigTxt，生成签名 Signature******************/
    $Signature = base64_encode(hash_hmac('sha1', $SigTxt, $secretKey, true));


    /***************拼接请求串,对于请求参数及签名，需要进行urlencode编码********************/
    $Req = "Signature=".urlencode($Signature);
    foreach ($ReqParaArray as $key => $value)
    {
        $Req=$Req."&".$key."=".urlencode($value);
    }

    /*********************************发送请求********************************/
    if($HttpMethod === 'GET')
    {
        if($isHttps === true)
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
        if($isHttps === true)
        {
            $Rsp= SendPost("https://".$FullHttpUrl,$Req,$isHttps);
        }
        else
        {
            $Rsp= SendPost("http://".$FullHttpUrl,$Req,$isHttps);
        }
    }

    var_export(json_decode($Rsp,true));
}

function SendPost($FullHttpUrl,$Req,$isHttps)
{

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $Req);

        curl_setopt($ch, CURLOPT_URL, $FullHttpUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($isHttps === true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  false);
        }

        $result = curl_exec($ch);

        return $result;
}


	public function get_part_by_tagid_themeid(){
		$ret = [
			'r' => -1,
			'msg' => '',
			'data' => '',
		];
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		if($tag_id > 0 && $themeid > 0){
			
			$tag = model('Tag');
			$ret['data'] = $tag->get_tag_by_themeid();
			$ret['r'] = 0;
		}else{
			$ret['msg'] = '传入参数不合法';	
		}
		return json($ret);
	}
	
	public function get_dim_name_by_tagid(){
		$ret = [
			'r' => -1,
			'msg' => '',
			'data' => '',
		];
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		$part = trim(input('part'));
		if($tag_id > 0 && $themeid > 0 && strlen($part) > 0){
			$tag = model('Tag');
			$ret['data'] = $tag->get_dim_tag_by_pid_themeid();
			$ret['r'] = 0;
		}else{
			$ret['msg'] = '传入参数不合法';
		}
		return json($ret);
	}
	
	public function getAll(){
		$result = [
			'r' => 0,
			'msg' => '',
			'data' => '',
		];
		$tag = model('Tag');
		$res = $tag->selectAll();
		$result['data'] = $res;
		return json($result);
	}
	
	public function add(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$pid = input('pid');
		$name = input('name');
		$themeid = empty(input('themeid'))?1:input('themeid');
		if($pid > 0){
			$res = Db::query('call addTag(:pid,:name,:themeid)',['pid'=>$pid,'name'=>$name,'themeid'=>$themeid]);
			if(count($res) > 0 && $res[0][0]['result'] == 1000){
				$result['r'] = 0;
				$result['msg']  = '添加成功';
			}else{
				$result['msg'] = '添加失败';
			}
		}else{
			$result['msg'] = 'pid 参数不符合要求';
		}
		return json($result);
		exit;
	}
	
	public function del(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$pid = input('pid');
		if($pid > 0){
			$res = Db::query('call delTag(:pid)',['pid'=>$pid]);
			if(count($res) > 0 && $res[0][0]['result'] == 1000){
				$result['r'] = 0;
				$result['msg'] = '删除成功';
			}else{
				$result['msg'] = '删除失败';
			}
		}else{
			$result['msg'] = 'pid 参数不符合要求';
		}
		return json($result);
		exit;
	}
	
	public function move(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$pid = input('pid');
		$tid = input('tid');
		if( $pid > 0 && $tid > 0){
			$res = Db::query('call moveTag(:pid,:tid)',['pid'=>$pid,'tid'=>$tid]);
			if(count($res) > 0 && $res[0][0]['result'] == 1000){
				$result['r'] = 0;
				$result['msg'] = '移动成功';
			}else{
				$result['msg'] = '移动失败';
			}
		}else{
			$result['msg'] = 'pid 参数不符合要求';
		}
		return json($result);
		exit;
	}
	
}






?>