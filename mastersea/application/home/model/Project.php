<?php
namespace app\home\model;
use think\Model;
use think\Db;


class Project extends Model{
	protected $table = 'project';
	
	public function get_project_by_id(){
		
		$project_id = input('project_id');
		$sql = 'SELECT * FROM project WHERE project_id = :project_id';
		$res = Db::query( $sql, ['project_id' => $project_id]);
		
		return $res;
	}
	
	public function get_latest_hot_project(){
		
		$sql = 'SELECT p.project_id,p.name,p.type,p.status,p.praise_num,p.intro,u.name username,s.src_name,s.path
				FROM project AS p LEFT JOIN user_project AS up ON p.project_id = up.project_id 
					 LEFT JOIN user u ON up.user_id = u.user_id 
					 LEFT JOIN src_relation sr ON p.user_id = sr.user_id
					 LEFT JOIN src s ON sr.src_id = s.src_id
				WHERE sr.type = 3 ORDER BY p.praise_num DESC LIMIT 10';
		$res = Db::query( $sql );
		return $res;
	}
	
}

?>