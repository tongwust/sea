<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Task extends Controller{
	
	//任务信息更新
	public function update_task_by_taskid(){
		$ret = [
			'r' => 0,
			'msg' => '修改成功',
		];
		$task_id = input('task_id');
		if( $task_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		$task = model('Task');
		$res = $task->updateTaskByTaskid();
		
		return json_encode( $ret );
	}
	
	//项目下的任务的删除
	public function delete_project_task(){
		$ret = [
			'r' => 0,
			'msg' => '修改成功',
		];
		$task_id = input('task_id');
		if( $task_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode( $ret );
			exit;
		}
		Db::startTrans();
		try{
			$project_task = model('ProjectTask');
			$task = model('Task');
			$src_relation = model('SrcRelation');
			
			$project_task->deleteByTaskid();
			$task->deleteByTaskid();
			$src_relation->deleteByTaskid();
			
			Db::commit();
		}catch(\Exception $e){
			$ret['r'] = -2;
			$ret['msg'] = $e;
			Db::rollback();
		}
		
		return json_encode( $ret );
	}
	
	
	
}


?>