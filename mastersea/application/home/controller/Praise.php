<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Praise extends Controller{
	
	public function add_praise(){
		$ret = [
			'r' => 0,
			'msg' => '点赞成功',
		];
		$cid = input('cid');//1 项目id,2 任务
		$user_id = input('user_id');
		$type = input('type');
		$opt = input('opt');//方式 1：加赞 2：减赞
		if( !($cid > 0 && $user_id > 0 && ($type == 1 || $type == 2) && ($opt == 1 || $opt == 2)) ){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		Db::startTrans();
		try{
			$praise = model('Praise');
			$project = model('Project');
			$task = model('Task');
			
			if( $type == 1){
				$res = $project -> updatePraiseNum( $opt );
			}else if( $type == 2){
				$res = $task -> updatePraiseNum( $opt );
			}
			if( $res <= 0){
				exception('数据修改失败', -3);
			}
			if( $opt == 1 ){
				$praise -> add_praise();
			}else if($opt == 2){
				$praise -> del_praise();
			}
			
			Db::commit();
		}catch( \Exception $e){
			$ret['r'] = -2;
			$ret['msg'] = $e->getMessage();
			Db::rollback();
		}
		return json_encode( $ret );
	}
	
	
}


?>