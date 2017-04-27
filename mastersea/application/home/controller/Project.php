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
	
	public function get_search_project_list(){
		$ret = [ 
			"r" => -1,
			"msg" => '',
			'data' => '',
		];
		
		$user_id = input('user_id');
		$search_content = input('search_content');
		$project = model('Project');
		
		if($search_content == '' || $search_content == null ){
			
			$res = $project->get_latest_hot_project();
			$project_id_arr = array_column( $res, 'project_id');
			
		}else{
			
		}
		return json($ret);
	}
	
	public function get_project_detail_by_id(){
		$ret = [ 
			"r" => -1,
			"msg" => '',
			'data' => '',
		];
		
		$project_id = input('project_id');
		$user_id = input('user_id');
		if($project_id > 0){
			
			$project = model('Project');
			$user_project_tag = model('UserProjectTag');
			$project_task = model('ProjectTask');
			$src_relation = model('SrcRelation');
			$comment = model('Comment');
			
			$ret['data'] = $res;
			$ret['data']['skill'] = $user_project_tag->get_tag_by_userid_projectid();
			$tasks = $project_task->get_task_src_comment_by_project_id();
			if( count($tasks) > 0){
				$task_arr = array_column($tasks, 'task_id');
				$src_arr = $src_relation->get_task_src_by_task_ids(implode(',', $task_arr), 2);//task
				foreach($tasks as &$v){
					foreach($src_arr as $value){
						if($value['task_id'] == $v['task_id']){
							array_push($v['src'],$value);
						}else{
							break;
						}
					}
				}
				$comment_arr = $comment->get_task_comment_by_task_ids(implode(',', $task_arr), 2);
				foreach($tasks as &$t){
					foreach($comment_arr as $c){
						if($t['task_id'] == $c['cid']){
							array_push($t['comment'], $c);
						}else{
							break;
						}
					}
				}
				$ret['data']['tasks'] = $tasks;
			}
			$ret['r'] = 0;
		}else{
			$ret['msg'] = '参数不符合要求';
		}
		return json($ret);
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
		$skill_ids = trim(input('skill_ids'));
		$tasks = json_decode(input('tasks'));
		
		$status = 0;	//待审
		$create_time = now();
		$project = model('Project');
		$task = model('Task');
		$project_task = model('ProjectTask');
		$user_project_tag = model('UserProjectTag');
		$user_task = model('UserTask');
		$src = model('Src');
		$src_relation = model('SrcRelation');
		$user_tim = new UserTim;
		
		Db::startTrans();
		
		try{
			$project->save();
			$project_id = $project->project_id;
			
			$skill_arr = explode(',' $skill_ids);
			if(count($skill_arr) > 0){
				$list = [];
				for( $i = 0; $i < count($skill_arr); $i++){
					array_push($list, ['user_id' = > $user_id,'project_id' => $project_id,'tag_id' => $skill_arr[$i]]);
				}
				$user_project_tag->save($list);
			}
			for($j = 0; $j < count($tasks); $j++){
				
				$task->title = $tasks[$j]['title'];
				$task->create_time = now();
				$task->save();
				$task_id = $task->task_id;
				
				$project_task->project_id = $project_id;
				$project_task->task_id = $task_id;
				$project_task->save();
				
				$user_task->user_id = $user_id;
				$user_task->task_id = $task_id;
				$user_task->save();
				
				$src->src_name = $tasks[$j]['src_name'];
				$src->type = $tasks[$j]['type'];
				$src->src_order = $tasks[$j]['src_order'];
				$src->path = $tasks[$j]['path'];
				$src->status = $tasks[$j]['status'];
				$src->create_time = now();
				$src->save();
				$src_id = $src->src_id;
				
				$src_relation->src_id = $src_id;
				$src_relation->relation_id = $task_id;
				$src_relation->type = 2;//任务
				$src_relation->save();
				
			}
			$user_tim->group_create_group($project_id,'Public', $name, $user_id, 1);// create group - 1:work 2:life
			Db::commit();
			$ret['r'] = 0;
			$ret['msg'] = '创建项目成功';
			$ret['project_id'] = $project_id;
		}catch( \Exception $e){
			Db::rollback();
			$ret['msg'] = '添加数据异常';
		}
		return json($ret);
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
				
			}
		}else{
			
		}
	}
	
}



?>