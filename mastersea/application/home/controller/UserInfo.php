<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class UserInfo extends Controller
{
	public function index(){
    	$view = new View();
    	return $view->fetch('./index');
    }
    public function get_user_part_info(){
    	$ret = [
    		'r' => 0,
    		'msg' => '查询成功',
    	];
    	$user_id = input('user_id');
    	if( $user_id > 0 ){
    		$user_info = model('UserInfo');
    		$user_attention = model('UserAttention');
    		$user_project_tag = model('UserProjectTag');
    		$user_tag = model('UserTag');
    		
    		$user_res = $user_info->get_user_detail_by_id();
    		if(count($user_res) > 0){
    			$ret = array_merge( $ret, $user_res[0]);
    			$atten_res = $user_attention->get_follow_users_by_id();
    			$project_res = $user_project_tag->get_project_by_userid();
    			
    			$tag_res = $user_tag->get_address_position_skill_interest_by_userid();
    			$ret['tags'] = $tag_res;
    			$ret['follow_num'] = count($atten_res);
    			$ret['project_num'] = count($project_res);
    		}else{
    			$ret['r'] = -2;
    			$ret['msg'] = '没有查询到user_id信息';
    		}

    	}else{
    		$ret['r'] = -1;
    		$ret['msg'] = '参数user_id不符合要求';
    	}
    	return json_encode($ret);
    }
    public function get_user_info(){
    	$ret = [
			'r' => -1,
			'msg' => '',
			'data' => [],
			'position' => [],
			'skill' => [],
			'interest' => [],
			'contact' => [],
			'language' => [],
		];
		$user_id = input('user_id');
    	$user_info = model('UserInfo');
    	$user_tag = model('UserTag');
    	$user_contact = model('UserContact');
    	if( $user_id > 0 ){
    		$position = $user_tag->get_tag_by_userid( 22, 10);
    		$skill = $user_tag->get_tag_by_userid( 30, 11);
    		$interest = $user_tag->get_tag_by_userid( 31, 12);
    		$language = $user_tag->get_tag_by_userid( 32, 13);
    		
    		$result = $user_info->get_user_detail_by_id();
    		$contact = $user_contact->get_user_contact_by_userid();
    		if( count($result) > 0 ){
    			$ret['data'] = json_encode($result[0]);
    			$ret['position'] = $position;
    			$ret['skill'] = $skill;
    			$ret['interest'] = $interest;
    			$ret['contact'] = $contact;
    			$ret['language'] = $language;
    		}
    	}else{
    		$ret['msg'] = '穿入的user_id不合法';
    	}
    	return json($ret);
    }
    
    public function update_user_info(){
    	$result = [
			'r' => -1,
			'msg' => '',
		];
    	$user_id = input('user_id');
//  	$name = trim(input('name'));
    	$sex = input('sex');
    	$birthday = input('birthday');
    	$fullname = trim(input('fullname'));
    	$en_name = trim(input('en_name'));
    	$area_id = input('area_id');
    	$curr_company = input('curr_company');
    	$short_name = input('short_name');
    	$work_age = input('work_age');
    	
    	$position_ids = input('position_ids');
    	$language_ids = input('language_ids');
    	$skill_ids = input('skill_ids');
    	
    	$education_school = input('education_school');
    	$history = input('history');
    	$intro = input('intro');
    	$contact = json_decode(input('contact'),true);
    	$latest_update_time = now();
//  	$user = new User;
    	$user_info = model('UserInfo');
    	$user_contact = model('UserContact');
    	$user_tag = model('UserTag');
    	if($user_id > 0){

    		Db::startTrans();
    		try{
//  			$user->allowField(['name'])->save($_POST,['user_id'=>$user_id]);
    			$user_info->allowField(['sex','birthday','fullname','en_name','area_id','curr_company','short_name','position','work_age','education_school','history','intro'])->save($_POST,['user_id'=>$user_id]);
    			if(count($contact) > 0){
    				$user_contact->saveAll($contact);
    			}
    			
    			$position_arr = explode(',' , $position_ids);
    			$position_list = [];
    			for($i = 0; $i < count($position_arr); $i++){
    				array_push($position_list,['user_id'=>$user_id,'tag_id' => $position_arr[$i],'user_tag_type'=>1]);
    			}
    			$user_tag->delete_user_tag( 22, 10);//职业
    			$user_tag->saveAll($position_list);
    			
    			$language_arr = explode(',' , $language_ids);
    			$language_list = [];
    			for($i = 0; $i < count($language_arr); $i++){
    				array_push($language_list,['user_id'=>$user_id, 'tag_id' => $language[$i],'user_tag_type'=>2]);
    			}
    			$user_tag->delete_user_tag( 32, 13);//工作语言
    			$user_tag->saveAll($language_list);
    			
    			$skill_arr = explode(',' , $skill_ids);
    			$skill_list = [];
    			for($i = 0; $i < count($skill_arr); $i++){
    				array_push($skill_list,['user_id'=>$user_id, 'tag_id' => $skill[$i],'user_tag_type'=>3]);
    			}
    			$user_tag->delete_user_tag( 30, 11);//技能
    			$user_tag->saveAll($skill_list);
    			
    			Db::commit();
    		}catch(\Exception $e){
    			Db::rollback();
    			$result['r'] = -6;
				$result['msg'] = '数据库错误!';
				exit;
    		}
    		$result['r'] = 0;
    		$result['msg'] = '修改成功';
    		return json($result);
    	}else{
    		$result['msg'] = 'user_id不符合要求';
    		return json($result);
    		exit;
    	}
    	
    }
    
}
?>