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
	
	public function get_members_info_by_project_id(){
		$ret = [ 
			"r" => 0,
			"msg" => '',
			'member_list' => [],
		];
		$project_id = input('project_id');
		
		if( $project_id > 0){
			$user_project_tag = model('UserProjectTag');
			$res = $user_project_tag->getMemberInfoByProjectId();
			$arr = [];
			foreach($res as $k => $v){
				$arr[$v['user_id']] = $v;
			}
			$ret['member_list'] = $arr;
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
		}
		return json_encode($ret);
	}
	
	public function delete_member_from_project(){
		
		$ret = [
			'r' => 0,
			'msg' => '删除成功',
		];
		$opt_id = input('opt_id');
		$member_id = input('member_id');
		$project_id = input('project_id');
		if( $opt_id > 0 && $member_id > 0 && $project_id > 0){
			
			$user_project_tag = model('UserProjectTag');
			$user_type_arr = $user_project_tag->getMemberType();
			$flag = false;
			foreach( $user_type_arr as $v){
				if( $v['user_type'] == 1){
					$flag = true;	break;
				}
			}
			if( $flag ){//1.负责人
				
				$user_project_tag->deleteMemberFromProject();
			}else{
				$ret['r'] = -2;
				$ret['msg'] = '操作人不是负责人';
			}
		}else{
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
		}
		return json_encode( $ret );
	}
	
	
	
	public function get_search_project_list(){
		header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
		$ret = [ 
			"r" => -1,
			"msg" => '',
			'project_list' => [],
		];
		$user_id = input('user_id');
		$search_content = input('search_content');
		$project = model('Project');
		$user_project_tag = model('UserProjectTag');
		
		if( empty($search_content) ){
			
			$res = $project->get_latest_hot_project();
			$project_id_arr = array_column( $res, 'project_id');
			
			$project_str = implode(',',$project_id_arr);
			$users = $user_project_tag->get_user_info_by_project_ids( $project_str );
			$members = $user_project_tag->get_project_members( $project_str );
			$member_num_arr = array();
			foreach( $members as $v){
				$member_num_arr[$v['project_id']] = isset($member_num_arr[$v['project_id']])?$member_num_arr[$v['project_id']] + 1:1;
				
			}
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
				foreach($member_num_arr as $p => $val){
					if( $v['project_id'] == $p){
						$v['member_num'] = $val;
						break;
					}
				}
			}
			$ret['project_list'] = $res;
			$ret['r'] = 0;
			$ret['msg'] = '获取成功';
		}else{
			
		}
		return json_encode($ret);
	}
	
	public function get_project_baseinfo(){
		$ret = [ 
			"r" => 0,
			"msg" => '查询成功',
			'project' => [],
		];
		$project_id = input('project_id');
		$user_id = input('user_id');
		if( $project_id <= 0 && $user_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符合要求';
			return json_decode($ret);
			exit;
		}
		$project = model('Project');
		$user_project_tag = model('UserProjectTag');
		$src_relation = model('SrcRelation');
		$project_tag = model('ProjectTag');
		
		$projectInfo = $project->get_project_by_id();
		$tags = $user_project_tag->get_tag_by_userid_projectid();
		$srcs = $src_relation->getSrcinfo( $project_id, 1, 1);
		$address = $project_tag->get_tag_by_project_id();
		
		$ret['project'] = (count($projectInfo) > 0)?array_merge( $ret['project'], $projectInfo[0]):[];
		$ret['project']['tags'] = (count($tags)> 0)?$tags:[];
		$ret['project']['srcs'] = (count($srcs)> 0)?$srcs:[];
		$ret['project']['address'] = (count($address) > 0)?$address[0]:[];
		return json_encode( $ret );
	}
	
	public function get_task_detail_by_projectid(){
		$ret = [
			"r" => -1,
			"msg" => '',
			'data' => [],
		];
		
		$project_id = input('project_id');
		$user_id = input('user_id');
		if($project_id > 0){
			
			$project = model('Project');
			$user_project_tag = model('UserProjectTag');
			$project_task = model('ProjectTask');
			$src_relation = model('SrcRelation');
			$comment = model('Comment');
//			$res = $project->get_project_by_id();
//			$ret['data'] = $res;
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
			$ret['msg'] = '查询成功';
		}else{
			$ret['msg'] = '参数不符合要求';
		}
		return json_encode($ret);
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
		$cover = json_decode(input('cover'),true);
		
		$status = 0;	//待审
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
			$project->save();
			
			$project_id = $project->project_id;
			$skill_arr = explode(',' , $skill_ids);
			if(count($skill_arr) > 0){
				$list = [];
				for( $i = 0; $i < count($skill_arr); $i++){
					array_push($list, ['user_id' => $user_id,'project_id' => $project_id,'tag_id' => $skill_arr[$i]]);
				}
				$user_project_tag->saveAll( $list );
			}
			foreach( $cover as $v ){
				$info = pathinfo($v['resource_path']);
				$path_arr = explode('/', $info['dirname']);
				$cover_arr = [
							'src_name'=> $info['basename'],
							'type'=> $v['type'],
							'src_order'=> $v['src_order'],
							'path'=> '/' . $path_arr[count($path_arr) - 1],
							'access_url'=>$v['access_url'],
							'access_url'=>$v['resource_path'],
							'access_url'=>$v['url'],
							'access_url'=>$v['source_url'],
							'status'=>$v['status']
							];
				$src->data( $cover_arr )->isUpdate(false)->save();
				$src_relation->data([ 'src_id' => $src->src_id, 'relation_id' => $project_id, 'type' => $v['type']])->isUpdate(false)->save();//项目3
			}
			$task_arr = [];
			for($j = 0; $j < count($tasks); $j++){
//				array_push($task_arr, ['title' => $tasks[$j]['title']]);
//				$task->title = $tasks[$j]['title'];
				$task->data(['title'=>$tasks[$j]['title']])->isUpdate(false)->save();
//				($j == 0)?$task->save():$task->isUpdate()->save();
				
				$task_id = $task->task_id;
				
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
				$info = pathinfo($tasks[$j]['resource_path']);
				$path_arr = explode('/', $info['dirname']);
				$src_arr = [
							'src_name' => $info['basename'],
							'type' => $tasks[$j]['type'],
							'src_order' => $tasks[$j]['src_order'],
							'path' => '/' . $path_arr[count($path_arr) - 1],
							'access_url' => $tasks[$j]['access_url'],
							'access_url' => $tasks[$j]['resource_path'],
							'access_url' => $tasks[$j]['url'],
							'access_url' => $tasks[$j]['source_url'],
							'status' => $tasks[$j]['status']
							];
				$src->data( $src_arr )->isUpdate(false)->save();
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
//			$user_tim->group_create_group($project_id,'Public', $name, $user_id, 1);// create group - 1:work 2:life
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