<?php
namespace app\home\model;
use think\Model;
use think\Db;

class UserAttention extends Model{
	
	protected $table = 'user_attention';

	public function getAttenUserinfo(){
		$follow_user_id = input('user_id');
		$sql = 'SELECT ua.user_id,u.name as username,ui.en_name,ui.fullname,s.src_id head_src_id,s.src_name head_src_name,s.path head_path,
						s.resource_path head_resource_path,s.access_url head_access_url,s.source_url head_source_url,s.url as head_url
				FROM user_attention as ua LEFT JOIN user as u ON ua.user_id = u.user_id
					LEFT JOIN user_info as ui ON u.user_id = ui.user_id
					LEFT JOIN src_relation as sr ON sr.relation_id = u.user_id && sr.type = 3
					LEFT JOIN src as s ON sr.src_id = s.src_id
				WHERE ua.follow_user_id = :follow_user_id';
		$res = Db::query( $sql, ['follow_user_id' => $follow_user_id ]);
		
		return $res;
	}
	
	public function getAttenMeUserList( $user_id){
		$from = input('from')?input('from'):0;
		$page_size = input('page_size')?input('page_size'):35;
		
		$sql = 'SELECT ua.user_id,u.name user_name,s.src_id user_src_id,s.access_url user_access_url
				FROM user_attention AS ua LEFT JOIN user AS u ON ua.user_id = u.user_id
					LEFT JOIN src_relation AS sr ON sr.relation_id = u.user_id && sr.type = 3
					LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 2
				WHERE ua.follow_user_id = :follow_user_id
					LIMIT '.$from.','.$page_size;
		$res = Db::query($sql, ['follow_user_id' => $user_id]);
		
		return $res;
	}
	public function getMyAttenUserList( $user_id){
		$from = input('from')?input('from'):0;
		$page_size = input('page_size')?input('page_size'):35;
		
		$sql = 'SELECT ua.follow_user_id user_id,u.name user_name,s.src_id user_src_id,s.access_url user_access_url
				FROM user_attention AS ua LEFT JOIN user AS u ON ua.follow_user_id = u.user_id
					LEFT JOIN src_relation AS sr ON sr.relation_id = u.user_id && sr.type = 3
					LEFT JOIN src AS s ON s.src_id = sr.src_id && s.type = 2
				WHERE ua.user_id = :user_id
					LIMIT '.$from.','.$page_size;
		$res = Db::query($sql, ['user_id' => $user_id]);
		
		return $res;
	}
	public function get_follow_users_by_id(){
		
		$follow_user_id = input('user_id');
		$sql = 'SELECT user_id
				FROM user_attention
				WHERE follow_user_id=:follow_user_id';
		$res = Db::query( $sql, ['follow_user_id' => $follow_user_id]);
		
		return $res;
	}
	public function getMyAttenUsersByUserId(){
		$sql = 'SELECT follow_user_id
				FROM user_attention
				WHERE user_id = :user_id';
		$res = Db::query( $sql, ['user_id' => input('user_id')]);
		
		return $res;
	}
	public function getAttenNumByUserids( $user_ids_str ){
		
		$sql = 'SELECT follow_user_id,count(user_id) as atten_num
				FROM user_attention
				WHERE follow_user_id in ('.$user_ids_str.') GROUP BY follow_user_id';
		
		$res = Db::query( $sql);
		return $res;
	}
}

?>