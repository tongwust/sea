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
    public function update_user_info(){
    	$result = [
			'r' => -1,
			'msg' => '',
		];
    	$user_id = input('user_id');
    	$name = trim(input('name'));
    	$sex = input('sex');
    	$birthday = input('birthday');
    	$fullname = trim(input('fullname'));
    	$en_name = trim(input('en_name'));
    	$area_id = input('area_id');
    	$curr_company = input('curr_company');
    	$short_name = input('short_name');
    	$position = input('position');
    	$work_age = input('work_age');
    	$education_school = input('education_school');
    	$history = input('history');
    	$intro = input('intro');
    	$latest_update_time = now();
    	$user = new User;
    	$user_info = new User_info;
    	if($user_id > 0){
    		if($name == ''){
    			$result['msg'] = '用户名不能为空';
	    		return json($result);
	    		exit;
    		}
    		Db::startTrans();
    		try{
    			$user->allowField(['name'])->save($_POST,['user_id'=>$user_id]);
    			$user_info->allowField(['sex','birthday','fullname','en_name','area_id','curr_company','short_name','position','work_age','education_school','history','intro'])->save($_POST,['user_id'=>$user_id]);
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