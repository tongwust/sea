<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Cache;

class UserAttention extends Controller{
	
	public function add_atten(){
		$ret = [
			'r' => 0,
			'msg' => '关注成功',
			'user_attention_id' => ''
		];
		$user_id = input('user_id');
		$follow_user_id = input('follow_user_id');
		if( !($user_id > 0 && $follow_user_id > 0 && $user_id != $follow_user_id) ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';			
			return json_encode( $ret );
			exit;
		}
		$user_attention = model('UserAttention');
		$user_attention -> data( ['user_id' => $user_id, 'follow_user_id' => $follow_user_id, 'relation_type' => (empty(input('relation_type'))?1:input('relation_type'))])->save();
		
		$ret['user_attention_id'] = $user_attention -> user_attention_id;
		return json_encode( $ret );
	}
	
	public function del_atten(){
		$ret = [
			'r' => 0,
			'msg' => '取消关注',
		];
		$user_attention_id = input('user_attention_id');
		if( $user_attention_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';			
			return json_encode( $ret );
			exit;
		}
		$user_attention = model('UserAttention');
		$user_attention -> destroy(['user_attention_id' => $user_attention_id]);
		
		return json_encode( $ret );
	}
}
?>