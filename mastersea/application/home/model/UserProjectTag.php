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
				WHERE user_id = :user_id GROUP BY project_id';
		
		$res = Db::query( $sql, ['user_id' => $user_id]);
		return $res;
	}
	public function getProjectListByUserid(){
		$from = empty(input('from'))?0:input('from');
		$page_size = empty(input('page_size'))?5:input('page_size');
		$user_id = input('user_id');
		
		$sql = 'SELECT DISTINCT(upt.project_id),upt.user_id,p.name as project_name,p.type,p.status,p.intro,p.praise_num,u.name AS username,s.src_name,s.path,s.resource_path,s.access_url,s.source_url,s.url
				FROM user as u LEFT JOIN user_project_tag as upt ON u.user_id = upt.user_id
					LEFT JOIN project as p ON upt.project_id = p.project_id
					LEFT JOIN src_relation as sr ON p.project_id = sr.relation_id && sr.type = 1
					LEFT JOIN src as s ON sr.src_id = s.src_id && s.type = 3
				WHERE u.user_id = :user_id LIMIT '.$from.','.$page_size;
		$res = Db::query( $sql, ['user_id' => $user_id ]);
		
		return $res;
	}
	public function get_tag_by_userid_projectid(){
		$project_id = input('project_id');
		$user_id = input('user_id');
		
		$sql = 'SELECT upt.tag_id,ti.name AS tag_name,upt.user_type
				FROM user_project_tag AS upt LEFT JOIN tag_info AS ti ON upt.tag_id = ti.tag_id
				WHERE upt.user_id = :user_id && upt.project_id = :project_id';
		$res = Db::query( $sql, ['user_id' => $user_id,'project_id' => $project_id]);
		
		return $res;
	}
	
	public function getMemberInfoByProjectId(){
		$project_id = input('project_id');
		
		$sql = 'SELECT upt.user_id,upt.tag_id,ti.name AS tag_name,u.name AS username,s.src_id,s.src_name,s.path,s.source_url
				FROM user_project_tag AS upt LEFT JOIN user AS u ON upt.user_id = u.user_id
					LEFT JOIN src_relation AS sr ON sr.type = 3 && u.user_id = sr.relation_id
					LEFT JOIN src AS s ON sr.src_id = s.src_id && s.type = 2
					INNER JOIN tag_info AS ti ON upt.tag_id = ti.tag_id
				WHERE upt.project_id = :project_id';
		$res = Db::query( $sql, ['project_id'=>$project_id] );
		return $res;
	}
	
	public function getMemberType(){
		$opt_id = input('opt_id');
		$project_id = input('project_id');
		
		$sql =  'SELECT user_type
				FROM user_project_tag
				WHERE user_id = :user_id && project_id = :project_id';
		$res = Db::query( $sql, ['user_id' => $opt_id, 'project_id' => $project_id]);
		
		return $res;
	}
	
	public function deleteMemberFromProject(){
		$project_id = input('project_id');
		$user_id = input('member_id');
		
		$sql = 'DELETE
				FROM user_project_tag
				WHERE project_id = :project_id && user_id = :user_id';
		$res = Db::query( $sql, ['project_id'=>$project_id, 'user_id' => $user_id]);
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
	
	public function getUserTags($project_ids_str){
	
		$sql = 'SELECT upt.project_id,upt.tag_id,ti.name
				FROM user_project_tag AS upt LEFT JOIN tag AS t ON upt.tag_id = t.tag_id
					LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				WHERE upt.project_id in('.$project_ids_str.')
					GROUP BY upt.project_id,upt.tag_id';
		$res = Db::query( $sql );
		return $res;
	}
	public function getUserTagsByProjectId($project_id){
		
		$sql = 'SELECT upt.project_id,upt.tag_id,ti.name
				FROM user_project_tag AS upt LEFT JOIN tag AS t ON upt.tag_id = t.tag_id
					LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				WHERE upt.project_id = :project_id
					GROUP BY upt.project_id,upt.tag_id';
		$res = Db::query( $sql, ['project_id' => $project_id]);
		return $res;
	}
	public function getUserProjectByUserids($user_ids_str){
		
		$sql = 'SELECT DISTINCT(upt.user_id),upt.project_id,upt.tag_id,upt.user_type,p.name project_name,ti.name as tag_name,
					s.src_name,s.path,s.resource_path,s.access_url,s.source_url,s.url
				FROM user_project_tag as upt LEFT JOIN project as p ON upt.project_id = p.project_id
					LEFT JOIN src_relation as sr ON sr.relation_id = p.project_id && sr.type = 1
					LEFT JOIN src as s ON sr.src_id = s.src_id
					INNER JOIN tag as t ON upt.tag_id = t.tag_id
					LEFT JOIN tag_info as ti ON t.tag_id = ti.tag_id
				WHERE user_id in ('.$user_ids_str.') && upt.user_type = 1';
		$res = Db::query( $sql );
		
		return $res;
	}
	public function getProjectNumByUserids($user_ids_str){
		
		$sql = 'SELECT user_id,project_id,tag_id
				FROM user_project_tag
				WHERE user_type = 1 && user_id in ('.$user_ids_str.')';
		$res = Db::query( $sql);
		
		return $res;
	}
//	public function getProjectMemberNum($project_ids_str){
//		
//		$sql = 'SELECT count(project_id) as member_num
//				FROM user_project_tag
//				WHERE project_id in ('.$project_ids_str.')
//				GROUP BY project_id,user_id';
//		$res = Db::query( $sql );
//		return $res;
//	}
}
?>