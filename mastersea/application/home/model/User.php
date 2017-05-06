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
	
	public function get_user_info_by_name_pwd(){
		$name = trim(input('name'));
		$pwd = md5(trim(input('pwd')));
		$sql = 'SELECT u.user_id,u.name,ui.sex,s.src_name,s.path
				FROM user AS u LEFT JOIN user_info AS ui ON u.user_id = ui.user_id
						LEFT JOIN src_relation AS sr ON sr.type = 3 && u.user_id = sr.relation_id
				  		LEFT JOIN src AS s ON sr.src_id = s.src_id
				WHERE u.name=:name && u.pwd=:pwd && u.status=1';
		$res = Db::query( $sql, ['name'=>$name,'pwd'=>$pwd] );
		return $res;
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