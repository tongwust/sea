<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Request;

class ProjectAttention extends Controller{
	
	public function add_pro_atten(){
		$ret = [
			'r' => 0,
			'msg' => '添加成功',	
			'project_attention_id' => '',
		];
		$project_id = input('project_id');
		$user_id = input('user_id');
		if( !($project_id > 0 && $user_id > 0) ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		$project_attention = model('ProjectAttention');
		
		$project_attention -> data(['project_id' => input('project_id'), 
								 'user_id' => input('user_id'),
								 'relation_type' => (empty(input('relation_type'))?1:input('relation_type'))
								 ]) -> save();
		$ret['project_attention_id'] = $project_attention -> project_attention_id;
		return json_encode( $ret );
	}
	
	public function del_pro_atten(){
		$ret = [
			'r' => 0,
			'msg' => '取消成功',
		];
		$project_attention_id = input('project_attention_id');	
		
		if( $project_attention_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		
		$project_attention = model('ProjectAttention');
		$res = $project_attention -> destroy(['project_attention_id' => $project_attention_id]);
		if( $res <= 0){
			$ret['r'] = -2;
			$ret['msg']	= '取消失败';
		}
		return json_encode( $ret);
	}

}

?>