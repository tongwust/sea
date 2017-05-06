<?php
namespace app\home\model;
use think\Model;
use think\Db;

class UserProjectTag extends Model{
	
	protected $table = 'user_project_tag';
	
	public function get_project_by_userid(){
		
		$user_id = input('user_id');
		$sql = 'SELECT project_id
				FROM user_project_tag
				WHERE user_id = :user_id GROUP BY project_id'
		
		$res = Db::query( $sql, ['user_id' => $user_id]);
		return $res;
	}
	
	public function get_tag_by_userid_projectid(){
		$project_id = input('project_id');
		$user_id = input('user_id');
		$sql = 'SELECT upt.tag_id,ti.tag_name
				FROM user_project_tag AS upt LEFT JOIN tag_info AS ti ON upt.tag_id = ti.tag_id
				WHERE upt.user_id = :user_id && upt.project_id = :project_id';
		$res = Db::query( $sql, ['user_id' => $user_id,'project_id' => $project_id]);
		return $res;
	}
	public function getMemberInfoByProjectId(){
		$project_id = input('project_id');
		
		$sql = 'SELECT upt.user_id,upt.tag_id,ti.name AS tag_name,u.name AS username,s.src_id,s.src_name,s.path
				FROM user_project_tag AS upt LEFT JOIN user AS u ON upt.user_id = u.user_id
					LEFT JOIN src_relation AS sr ON sr.type = 3 && u.user_id = sr.relation_id
					LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 2
					INNER JOIN tag_info AS ti ON upt.tag_id = ti.tag_id
				WHERE upt.project_id = :project_id';
		$res = Db::query( $sql, ['project_id'=>$project_id] );
		return $res;
	}
	
	public function get_user_info_by_project_ids($project_ids_str){
		
		$sql = 'SELECT DISTINCT(upt.project_id),upt.user_id,u.name as username,s.src_name,s.path
				FROM user_project_tag upt INNER JOIN user u ON upt.user_type = 1 && upt.user_id = u.user_id
					INNER JOIN src_relation sr ON sr.relation_id = u.user_id && sr.type = 3
					INNER JOIN src s ON sr.src_id = s.src_id
				WHERE upt.project_id in('.$project_ids_str.')';
		$res = Db::query( $sql );
		return $res;
	}
	
	public function get_project_members($project_ids_str){
		
		$sql = 'SELECT user_id,project_id
				FROM user_project_tag
				WHERE project_id in('.$project_ids_str.') 
					GROUP BY user_id,project_id';
		$res = Db::query( $sql );
		return $res;
		
	}
}
?>