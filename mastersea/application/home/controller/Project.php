<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Request;

class Project extends Controller{
	public function index(){
		$view = new View();
		return $view->fetch('./test/upload');
	}
	public function add(){
		$ret = [ 
			"r" => -1,
			"msg" => ''
		];
		$user_id = input('user_id');
		$name = trim(input('name'));
		$type = input('type');
		$en_name = trim(input('en_name'));
		$cat_name = trim(input('cat_name'));
		$address = trim(input('address'));
		$project_start_time = input('project_start_time');
		$project_end_time = input('project_end_time');
		$intro = trim(input('intro'));
		$status = 0;	//待审
		$create_time = now();
		$project = model('Project');
		$task = model('Task');
		$project_task = model('ProjectTask');
		$user_task = model('UserTask');
		$src = model('Src');
		Db::startTrans();
		
		try{
			$project->save();
			$project_id = $project->project_id;
			
			$task->create_time = now();
			$task->save();
			$task_id = $task->task_id;
			
			$project_task->project_id = $project_id;
			$project_task->task_id = $task_id;
			$project_task->save();
			
			$user_task->user_id = $user_id;
			$user_task->task_id = $task_id;
			$user_task->save();
			
			Db::commit();
		}catch( \Exception $e){
			Db::rollback();
			
		}
	}
	
	public function upload(Request $request){
		$ret = [ 
			"r" => -1,
			"msg" => ''
		];
		$user_id = input('user_id');
		$files = request()->file('image');
		if( is_array($files) ){
			foreach($files as $file){
				
				$info = $file->validate(['size'=>10*1024*1024,'ext'=>'jpg,png,gif,jpeg,bmp'])->move(ROOT_PATH.'public/upload/img/'.$user_id.'/');
				if( $info ){
					dump($info);
					
				}else{
					
				}
				
			}
		}else{
			
		}
	}
	
}



?>