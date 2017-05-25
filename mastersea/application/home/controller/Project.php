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
			"msg" => '查询成功',
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
	//yunsou添加项目内容 all
	public function add_project_search_keys(){
		header("Access-Control-Allow-Origin:*");
    	header("Access-Control-Allow-Method:POST,GET");
		
		$project_tcs = new TcsQcloudApi(58740002);
		
		$res = $project_tcs -> projectDataManipulation();
		
		return json_encode($res);
	}
	
	//向腾讯云添加 搜索记录
	public function add_search_key_by_project_id($project_id){
//		$project_id = input('project_id');
		$project_tcs = new TcsQcloudApi(58740002);
		
		$project_tcs->DataManipulationByProjectId( $project_id );
		
	}
	
	public function get_user_project_list(){
		$ret = [ 
			"r" => 0,
			"msg" => '查询成功',
			'project_list' => [],
		];
		$user_id = input('user_id');
		if( $user_id <= 0){
			$ret['r'] = -1;
			$ret['msg'] = '参数不符';
			return json_encode($ret);
			exit;
		}
		$user_project_tag = model('UserProjectTag');
		$project_atten = model('ProjectAttention');
		
		$res = $user_project_tag -> getProjectListByUserid();
		
		$project_id_arr = array_column( $res, 'project_id');
		$project_ids_str = implode( ',', $project_id_arr);
		
		$atten_arr = $project_atten -> getProjectAttenNum($project_ids_str);
		$arr = [];
		foreach($atten_arr as $val){
			$arr[$val['project_id']] = $val['atten_num'];
		}
		
		$members = $user_project_tag -> get_project_members( $project_ids_str );
		$member_num_arr = [];
		foreach( $members as $v){
			$member_num_arr[$v['project_id']] = isset($member_num_arr[$v['project_id']])?$member_num_arr[$v['project_id']] + 1:1;
		}
		foreach( $res as &$v){
			$v['atten_num'] = empty($arr[$v['project_id']])?0:$arr[$v['project_id']];
			$v['member_num'] = empty($member_num_arr[$v['project_id']])?0:$member_num_arr[$v['project_id']];
		}
		$ret['project_list'] = $res;
		
		return json_encode($ret);
	}
	
	public function get_search_project_list(){
		header("Access-Control-Allow-Origin:*"); 
    	header("Access-Control-Allow-Method:POST,GET");
		$ret = [
			"r" => 0,
			"msg" => '获取成功',
			'project_list' => [],
		];
		$user_id = input('user_id');
		$search_query = input('search_query');
		
		$project = model('Project');
		$user_project_tag = model('UserProjectTag');
		$project_tcs = new TcsQcloudApi( 58740002 );
		
		if( empty($search_query) ){
			
			$res = $project->get_latest_hot_project();
			$project_id_arr = array_column( $res, 'project_id');
			$project_ids_str = implode( ',', $project_id_arr);
		}else{
			//search
			$res_json = $project_tcs -> yunsouDataSearch();
			$data = json_decode( $res_json, true );//dump($data);
			if( $data['r'] == 0 && $data['data']['result_list'] ){
				$project_id_arr = array_column( $data['data']['result_list'], 'doc_id');
				$project_ids_str = implode( ',', $project_id_arr);
				$res = $project -> getSearchProjects( $project_ids_str );
			}
		}
		if( empty($project_ids_str) || $project_ids_str == ''){
			$ret['msg'] = '数据为空';
			return json_encode($ret);
			exit;
		}
		$users = $user_project_tag -> get_user_info_by_project_ids( $project_ids_str );
		$members = $user_project_tag -> get_project_members( $project_ids_str );
		$member_num_arr = array();
		foreach( $members as $v){
			
			$member_num_arr[$v['project_id']] = isset($member_num_arr[$v['project_id']])?$member_num_arr[$v['project_id']] + 1:1;
		}
		foreach($res as $k => &$v){
			foreach($users as $u){
				if( $v['project_id'] == $u['project_id'] ){
					$v['user_id'] = $u['user_id'];
					$v['username'] = $u['username'];
					$v['src_name'] = $u['src_name'];
					$v['path'] = $u['path'];
					break;
				}
			}
			$v['member_num'] = empty($member_num_arr[$v['project_id']])?0:$member_num_arr[$v['project_id']];
		}
		$ret['project_list'] = $res;
		
		return json_encode($ret);
	}
	//项目基本信息
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
	//项目中任务详情
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
		if( $user_id <= 0){
			$ret['msg'] = '用户id 不能为空';
			return json_encode($ret);
			exit;
		}
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
//			$project->type = $type;
//			$project->en_name = $en_name;
//			$project->cat_name = $cat_name;
//			$project->address = $address;
//			$project->project_start_time = $project_start_time;
//			$project->project_end_time = $project_end_time;
			$project->intro = $intro;
			$project->save();
			
			$project_id = $project->project_id;
//			$skill_arr = explode(',' , $skill_ids);
//			if(count($skill_arr) > 0){
//				$list = [];
//				for( $i = 0; $i < count($skill_arr); $i++){
//					array_push($list, ['user_id' => $user_id,'project_id' => $project_id,'tag_id' => $skill_arr[$i]]);
//				}
//				$user_project_tag->saveAll( $list );
//			}
			
			foreach( $cover as $v ){
				$info = pathinfo($v['resource_path']);
				$path_arr = explode('/', $info['dirname']);
				$cover_arr = [
							'src_name'=> $info['basename'],
							'type'=> 3,
							'src_order'=> 0,
							'path'=> '/' . $path_arr[count($path_arr) - 1],
							'access_url'=>$v['access_url'],
							'resource_path'=>$v['resource_path'],
							'url'=>$v['url'],
							'source_url'=>$v['source_url'],
							'status'=> 0
							];
				$src->data( $cover_arr )->isUpdate(false)->save();
				$src_relation->data([ 'src_id' => $src->src_id, 'relation_id' => $project_id, 'type' => 2])->isUpdate(false)->save();//项目3
			}
			for($j = 0; $j < count($tasks); $j++){

				$task->data(['title'=>$tasks[$j]['title'],'description' => $tasks[$j]['description'] ])->isUpdate(false)->save();
				
				$task_id = $task->task_id;
				
				$project_task->data(['project_id'=>$project_id,'task_id'=>$task_id])->isUpdate(false)->save();

				$user_task->data(['user_id'=>$user_id,'task_id'=>$task_id])->isUpdate(false)->save();

				$info = pathinfo($tasks[$j]['resource_path']);
				$path_arr = explode('/', $info['dirname']);
				$src_arr = [
							'src_name' => $info['basename'],
							'type' => $tasks[$j]['type'],
							'src_order' => $tasks[$j]['src_order'],
							'path' => '/' . $path_arr[count($path_arr) - 1],
							'access_url' => $tasks[$j]['access_url'],
							'resource_path' => $tasks[$j]['resource_path'],
							'url' => $tasks[$j]['url'],
							'source_url' => $tasks[$j]['source_url'],
							'status' => $tasks[$j]['status']
							];
				$src->data( $src_arr )->isUpdate(false)->save();
				$src_id = $src->src_id;

				$src_relation->data(['src_id'=>$src_id,'relation_id'=>$task_id,'type'=>2])->isUpdate(false)->save();
			}
//			$user_tim->group_create_group($project_id,'Public', $name, $user_id, 1);// create group - 1:work 2:life
			Db::commit();
			$ret['r'] = 0;
			$ret['msg'] = '创建项目成功';
			$ret['project_id'] = $project_id;
		}catch( \Exception $e){
			Db::rollback();
			$ret['msg'] = '添加数据异常'.$e;
		}
		$this->add_search_key_by_project_id($project_id);
		return json_encode($ret);
	}
	
	//项目删除
	
	
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