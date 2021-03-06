<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Msg extends Controller{
	
	public function send_single_msg(){
		$ret = [
			'r' => 0,
			'msg' => '发送成功',
		];
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode($ret);
			exit;
		}
		$send_user_id = session('userinfo')['user_id'];
		
//		$send_user_id = input('send_user_id');
//		$send_user_id = 3;//test
		
		$receive_user_id = input('receive_user_id');
		$msg_content = trim( input('msg_content'));
		if($send_user_id <= 0 || $receive_user_id <= 0 || empty($msg_content)){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$msg_text = model('MsgText');
		$msg = model('Msg');
		
		Db::startTrans();
		try{
			$msg_text -> msg_content = $msg_content;
			$msg_text -> msg_title = input('msg_title');
			$msg_text -> save();
			
			$msg -> msg_id = $msg_text->msg_id;
			$msg -> send_user_id = $send_user_id;
			$msg -> receive_user_id = $receive_user_id;
			$msg -> save();
			$ret['msg_id'] = $msg_text->msg_id;
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			$ret['r'] = -2;
			$ret['msg'] = '数据库操作出错'.$e;
		}
		return json_encode($ret);
	}
	
	public function get_my_send_msgs(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',
			'mlist'	=> [],
		];
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = 3;
		$msg = model('Msg');
		
		$res = $msg -> getMySendMsgs( $user_id );
		$ret['mlist'] = $res;
//		dump($ret);
		return json_encode($ret);
	}
	
	public function get_my_receive_msgs(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',
			'mlist'	=> [],
		];
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = 3;
		$msg = model('Msg');
		
		$res = $msg -> getMyReceiveMsgs( $user_id );
		$ret['mlist'] = $res;
//		dump($ret);
		return json_encode($ret);
	}
	
	public function del_single_msg(){
		$ret = [
			'r' => 0,
			'msg' => '删除成功',
		];
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = 3;
		$send_user_id = input('send_user_id');
		$receive_user_id = input('receive_user_id');
		$msg_id = input('msg_id');
		if( $send_user_id <= 0 || $receive_user_id <= 0 || $msg_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$msg = model('Msg');
		$res = $msg -> delSingleMsg();
//		Db::startTrans();
//		try{
//			$msg -> destroy(['send_user_id' => $send_user_id,'receive_user_id'=> $receive_user_id,'msg_id' => $msg_id]);
//			$msg_text -> destroy(['msg_id' => $msg_id]);
//			
//			Db::commit();
//		}catch(\Exception $e){
//			Db::rollback();
//			$ret['r'] = -2;
//			$ret['msg'] = '数据库错误'.$e;
//		}
		
		return json_encode($ret);
	}
	
	public function change_single_msg_status(){
		$ret = [
			'r' => 0,
			'msg' => '修改成功',
		];
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = 3;
		$send_user_id = input('send_user_id');
		$receive_user_id = input('receive_user_id');
		$msg_id = input('msg_id');
		if( $send_user_id <= 0 || $receive_user_id <= 0 || $msg_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$msg = model('Msg');
		$res = $msg -> changeSingleMsgStatus();
		
		return json_encode($ret);
	}
	
	public function get_unread_msg_num(){
		$ret = [
			'r' => 0,
			'msg' => '查询成功',
		];
		if( !session('userinfo') ){
			$ret['r'] = -100;
			$ret['msg'] = '未登录';
			return json_encode($ret);
			exit;
		}
		$user_id = session('userinfo')['user_id'];
//		$user_id = 3;
		$msg = model('Msg');
		
		$res = $msg -> getUnreadMsgNum($user_id);
		$ret['msg_num'] = (count($res) > 0)?$res[0]['msg_num']:0;
		
		return json_encode($ret);
	}
	
}


?>