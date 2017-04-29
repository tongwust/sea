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
		header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
		$ret = [ 
			"r" => -1,
			"msg" => '',
			'data' => '',
		];
		
		$user_id = input('user_id');
		$search_content = input('search_content');
		$project = model('Project');
		$user_project_tag = model('UserProjectTag');
		
		if($search_content == '' || $search_content == null ){
			
			$res = $project->get_latest_hot_project();dump($res);
			$project_id_arr = array_column( $res, 'project_id');
			
			$project_str = implode(',',$project_id_arr);
			$users = $user_project_tag->get_user_info_by_project_ids($project_str);dump($users);
			foreach($res as $k => &$v){
				foreach($users as $u){
					if($v['project_id'] == $u['project_id'] ){
						$v['user_id'] = $u['user_id'];
						$v['username'] = $u['username'];
						$v['src_name'] = $u['src_name'];
						$v['path'] = $u['path'];
						break;
					}
				}
			}
			$ret['data'] = json_encode($res);
			$ret['r'] = 0;
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
		$tasks = json_decode(input('tasks'),true);
		
		$status = 0;	//待审
//		$create_time = time();
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
			$project->name = $name;
			$project->type = $type;
			$project->en_name = $en_name;
			$project->cat_name = $cat_name;
			$project->address = $address;
			$project->project_start_time = $project_start_time;
			$project->project_end_time = $project_end_time;
			$project->intro = $intro;
//			$project->create_time = $create_time;
			$project->save();
			$project_id = $project->project_id;
			$skill_arr = explode(',' , $skill_ids);
			if(count($skill_arr) > 0){
				$list = [];
				for( $i = 0; $i < count($skill_arr); $i++){
					array_push($list, ['user_id' => $user_id,'project_id' => $project_id,'tag_id' => $skill_arr[$i]]);
				}
				$user_project_tag->saveAll($list);
			}
			$task_arr = [];
			for($j = 0; $j < count($tasks); $j++){
//				array_push($task_arr, ['title' => $tasks[$j]['title']]);
//				$task->title = $tasks[$j]['title'];
				$task->data(['title'=>$tasks[$j]['title']])->isUpdate(false)->save();
//				($j == 0)?$task->save():$task->isUpdate()->save();
				
				
				$task_id = $task->task_id;dump($tasks[$j]);
				
//				$project_task->project_id = $project_id;
//				$project_task->task_id = $task_id;
				$project_task->data(['project_id'=>$project_id,'task_id'=>$task_id,'t_type'=>$tasks[$j]['type']])->isUpdate(false)->save();
//				($j == 0)?$project_task->save():$project_task->isUpdate()->save();
				
//				$user_task->user_id = $user_id;
//				$user_task->task_id = $task_id;
//				($j == 0)?$user_task->save():$user_task->isUpdate()->save();
				$user_task->data(['user_id'=>$user_id,'task_id'=>$task_id])->isUpdate(false)->save();
//				$src->src_name = $tasks[$j]['src_name'];
//				$src->type = $tasks[$j]['type'];
//				$src->src_order = $tasks[$j]['src_order'];
//				$src->path = $tasks[$j]['path'];
//				$src->status = $tasks[$j]['status'];
//				($j == 0)?$src->save():$src->isUpdate()->save();
				$src->data(['src_name'=>$tasks[$j]['src_name'],'type'=>$tasks[$j]['type'],'src_order'=>$tasks[$j]['src_order'],'path'=>$tasks[$j]['path'],'status'=>$tasks[$j]['status']])->isUpdate(false)->save();
				$src_id = $src->src_id;
//				
//				$src_relation->src_id = $src_id;
//				$src_relation->relation_id = $task_id;
//				$src_relation->type = 2;//任务
//				($j == 0)?$src_relation->save():$src_relation->isUpdate()->save();
				$src_relation->data(['src_id'=>$src_id,'relation_id'=>$task_id,'type'=>2])->isUpdate(false)->save();
			}
//			$task->saveAll($task_arr);
//			dump($task->task_id);
			$user_tim->group_create_group($project_id,'Public', $name, $user_id, 1);// create group - 1:work 2:life
			Db::commit();
			$ret['r'] = 0;
			$ret['msg'] = '创建项目成功';
			$ret['project_id'] = $project_id;
		}catch( \Exception $e){
			Db::rollback();
			$ret['msg'] = '添加数据异常'.$e;
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