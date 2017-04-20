<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Cache;
use think\Config;
use think\Loader;

use sms\SmsSingleSender;

class Index extends Controller
{
	public function index(){
    	$view = new View();
    	return $view->fetch('./index');
    }
    public function check(){
    	$result = [
			'r' => -1,
			'msg' => '',
		];
		$user = model('User');
		if($user->check_name() > 0){
			$result['r'] = -2;
			$result['msg'] = '用户名已存在';
		}else{
			$result['r'] = 0;
		}
		return json($result);
		exit;
    }
    public function sendMsg(){
    	$result = [
			'r' => -1,
			'msg' => '',
		];
		$mobile = trim(input('mobile'));
		$pattern_mobile = '/^1[3|4|5|8][0-9]\d{4,8}$/';
		if(!preg_match( $pattern_mobile, $mobile)){
		    $result['r'] = -4;
			$result['msg'] = '格式错误';
			return json($result);
			exit;
		}
		try{
			$appid = 1400028629;
			$appkey = 'ac63e8e5a3ee3982de81c35bc6fcf1d6';
			$tmpid = 15906;
			Loader::import('sms\SmsSingleSender', EXTEND_PATH);
			$singleSender = new SmsSingleSender($appid,$appkey);
			$code = mt_rand(1000,9999);
			$params = array($code,"60");
			$res = $singleSender->send(0,"86",$mobile,"注册的验证码为".$code."，有效期为60秒。","","");
//			$res = $singleSender->sendWithParam('86',$mobile,$tmpid,$params,"shining","","");
			$res = json_decode($res,true);
			if($res['result'] == 0){
				cache($mobile,$code,60);
				$result['r'] = 0;
				$result['msg'] = '发送成功';
				return json($result);
				exit;
			}else{
				$result['msg'] = '发送短信失败';
				return json($result);
				exit;
			}
		}catch(\Exception $e){
			$result['msg'] = '发送短信出错'.$e;
			return json($result);
			exit;
		}
    }
    public function check_code(){
    	$result = [
			'r' => -1,
			'msg' => '',
		];
    	$code = trim(input('code'));
    	$mobile = trim(input('mobile'));
    	if(cache($mobile) == $code){
    		$result['r'] = 0;
    		$result['msg'] = '验证通过';
    	}else{
    		$result['msg'] = '未通过';
    	}
    	return json($result);
    }
	public function register(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$user = model('User');
		$name = trim(input('name'));
		$pwd = trim(input('pwd'));
		$repwd = trim(input('repwd'));
		$mobile = trim(input('mobile'));
		if($name == '' || $pwd == '' || $repwd == '' || $mobile == ''){
			$result['r'] = -5;
			$result['msg'] = '用户名 密码或邮箱不能为空！';
			return json($result);
			exit;
		}
		if(input('pwd') != input('repwd')){
			$result['r'] = -3;
			$result['msg'] = '两次输入的密码不一致';
			return json($result);
			exit;
		}
		if($user->check_name() > 0){
			$result['r'] = -2;
			$result['msg'] = '用户名已存在';
			return json($result);
			exit;
		}
		$pattern_email="/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
		$pattern_mobile = '/^1[3|4|5|8][0-9]\d{4,8}$/';
		if(!preg_match( $pattern_mobile, $mobile)){
		    $result['r'] = -4;
			$result['msg'] = '格式错误';
			return json($result);
			exit;
		}
		//创建用户
		$user->name = trim(input('name'));
		$user->pwd = md5(trim(input('pwd')));
		$user->status = 1;
		$contract = model('Contract');
		$user_info = model('UserInfo');
		$user_info->position = input('position');
		$contract->contract = $mobile;
		$contract->type = 1;
		Db::startTrans();
		try{
			$user->save();
			$contract->user_id = $user->user_id;
			$user_info->user_id = $user->user_id;
			$contract->save();
			$user_info->save();
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			$result['r'] = -6;
			$result['msg'] = '数据库错误!';
			exit;
		}
		$result['r'] = 0;
		$result['mgs'] = '添加成功！';
		return json($result);
		//send email
		//$this->email($user->name,$contract->email,md5($user->name.$user->pwd.$user->user_id),$user->user_id);
	}
	public function user_login(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$user = model('User');
		$name = trim(input('name'));
		$pwd = trim(input('pwd'));
		if($name == '' || $pwd == ''){
			$result['msg'] = '用户名或密码不能为空';
			return json($result);
			exit;
		}
		$res = $user->check_name_pwd();
		if(count($res) > 0){
			$result['r'] = 0;
			$result['msg'] = '登陆成功';
			session([
			    'prefix'     => 'think',
			    'type'       => '',
			    'auto_start' => true,
			    'expire'	 => 7*24*3600,
			    'use_cookies'=> true,
			]);
			session('user.name',$res[0]['name']);
			session('user.user_id',$res[0]['user_id']);
			
		}else{
			$result['msg'] = '用户名或密码错误';
		}
		return json($result);
	}
	public function user_logout(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		session('user',null);
		$result['r'] = 0;
		return json($result);
	}
	public function change_user_status(){
		$str = input('str');
		$user_id = input('id');
		if($str == '' || $user_id){
			echo '<script>alert("错误的链接地址！");</script>';
			exit;
		}
		$user = model('User');
		$user->save([
			'status'=>1,
		],['user_id'=>$user_id]);
	}
	public function email($name,$toemail,$str,$user_id) {
        $subject='新注册账户激活邮件';
        $content='恭喜你，邮件发送成功。 <a href="'.url('home/index/change_user_status',['str'=>$str,'id'=>$user_id]).'">点此链接激活账号</a>';
        send_mail($toemail,$name,$subject,$content);
    }
	
	
}

?>