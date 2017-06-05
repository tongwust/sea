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
    //个人详细信息
    public function get_user_info(){
    	$ret = [
			'r' => 0,
			'msg' => '查询成功',
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
    	$user_project_tag = model('UserProjectTag');
    	$user_attention = model('UserAttention');
    	$project_attention = model('ProjectAttention');
//  	dump(session('userinfo'));
//  	if( !session('userinfo') ){
//  		$ret['r'] = -100;
//  		$ret['msg'] = '未登录，请登录';
//  		return json_encode( $ret);
//  	}
//  	$user_id = session('userinfo')['user_id'];
//  	$ret['msg'] = $user_info;
    	if( $user_id > 0 ){
    		$position = $user_tag->get_tag_by_userid( 22, 10);
    		$skill = $user_tag->get_tag_by_userid( 30, 11);
    		$interest = $user_tag->get_tag_by_userid( 31, 12);
    		$language = $user_tag->get_tag_by_userid( 32, 13);
    		
    		$result = $user_info->get_user_detail_by_id();
    		$partners_num = $user_project_tag -> getPartnersNumByUserId();
    		$parr = [];
    		foreach( $partners_num as $v){
    			$parr[$v['project_id']] = $v['user_num'];
    		}
//  		dump($partners_num);
    		$by_atten_unum = $user_attention -> get_follow_users_by_id();
    		$my_atten_unum = $user_attention -> getMyAttenUsersByUserId();
    		$by_atten_pnum = $user_project_tag -> getProjectAttenNum();
    		$project_res = $user_project_tag->get_project_by_userid();
    		$byArr = [];
    		foreach( $by_atten_pnum as $val){
    			$byArr[$v['project_id']] = $v['user_num'];
    		}
    		$my_atten_pnum = $project_attention -> getMyAttenProjectNum();
    		
    		$contact = $user_contact->get_user_contact_by_userid();
    		if( count($result) > 0 ){
    			$ret['data'] = $result[0];
    			$ret['data']['partners_num'] = array_sum($parr);
    			$ret['data']['by_atten_unum'] = count($by_atten_unum);
    			$ret['data']['my_atten_unum'] = count($my_atten_unum);
    			$ret['data']['by_atten_pnum'] = array_sum($byArr);
    			$ret['data']['my_atten_pnum'] = $my_atten_pnum[0]['my_atten_pnum'];
    			$ret['data']['project_num'] = count($project_res);
    			$ret['position'] = $position;
    			$ret['skill'] = $skill;
    			$ret['interest'] = $interest;
    			$ret['contact'] = $contact;
    			$ret['language'] = $language;
    		}
    	}else{
    		$ret['r'] = -1;
    		$ret['msg'] = '传入的user_id不合法';
    	}
//  	dump($ret);
    	return json_encode($ret);
    }
    
    public function update_user_info(){
    	$result = [
			'r' => -1,
			'msg' => '',
		];
    	$user_id = input('user_id');
    	$sex = input('sex');
    	$birthday = input('birthday');
    	$fullname = trim(input('fullname'));
    	$en_name = trim(input('en_name'));
    	$area_id = input('area_id');
    	$curr_company = input('curr_company');
    	$short_name = input('short_name');
    	$position_ids = input('position_ids');
    	$language_ids = input('language_ids');
    	$skill_ids = input('skill_ids');
    	
    	$education_school = input('education_school');
    	$history = input('history');
    	$intro = input('intro');
    	$contact = json_decode(input('contact'),true);
    	$latest_update_time = time();
    	
    	$user_info = model('UserInfo');
    	$user_contact = model('UserContact');
    	$user_tag = model('UserTag');
    	if($user_id > 0){
    		Db::startTrans();
    		try{
    			$res = $user_info->allowField(['sex','birthday','fullname','en_name','area_id','curr_company','en_company','short_name','education_school','intro'])->save(input(),['user_id'=>$user_id]);
    			if(count($contact) > 0){
    				$res = $user_contact->saveAll($contact);
    			}
    			$position_arr = (strlen($position_ids) == 0)?[]:explode(',' , $position_ids);
    			$position_list = [];
    			for($i = 0; $i < count($position_arr); $i++){
    				array_push($position_list,['user_id'=>$user_id,'tag_id' => $position_arr[$i],'user_tag_type'=>1]);
    			}
    			$user_tag->delete_user_tag( 22, 10);//职业
    			$user_tag->saveAll($position_list);
    			
    			$language_arr = (strlen($language_ids) == 0)?[]:explode(',' , $language_ids);
    			$language_list = [];
    			for($i = 0; $i < count($language_arr); $i++){
    				array_push($language_list,['user_id'=>$user_id, 'tag_id' => $language_arr[$i],'user_tag_type'=>2]);
    			}
    			$user_tag->delete_user_tag( 32, 13);//工作语言
    			if( count($language_list) > 0){
    				$user_tag->saveAll($language_list);
    			}
    			$skill_arr = (strlen($skill_ids) == 0)?[]:explode(',' , $skill_ids);
    			$skill_list = [];
    			for($i = 0; $i < count($skill_arr); $i++){
    				array_push($skill_list,['user_id'=>$user_id, 'tag_id' => $skill[$i],'user_tag_type'=>3]);
    			}
    			$user_tag->delete_user_tag( 30, 11);//技能
    			if( count($skill_list) > 0){
    				$user_tag->saveAll($skill_list);
    			}
    			$result['r'] = 0;
    			$result['msg'] = '修改成功';
    			Db::commit();
    		}catch(\Exception $e){
    			Db::rollback();
    			$result['r'] = -6;
				$result['msg'] = '数据库错误!'.$e;
    		}
    	}else{
    		$result['msg'] = 'user_id不符合要求';
    	}
    	
    	return json_encode($result);
    }
    
    
    
}
?>