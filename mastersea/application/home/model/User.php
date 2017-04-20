<?php
namespace app\home\model;
use think\Model;
use think\Db;

class User extends Model{
	
	protected $table = 'user';
	
	public function check_name(){
		$name = input('name');
		$res = Db::query('select user_id 
    							from user
    							where name=:name',
    							['name'=>$name]);
    	return count($res);
	}
	public function check_name_pwd(){
		$name = trim(input('name'));
		$pwd = md5(trim(input('pwd')));
		$res = Db::query('select user_id,name
								from user
								where name=:name && pwd=:pwd && status=1',
								['name'=>$name,'pwd'=>$pwd]);
		return $res;
		
	}
	public function get_user_by_id(){
		$user_id = input('id');
		
	}
	
}


?>