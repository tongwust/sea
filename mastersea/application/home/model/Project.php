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
		
		$sql = 'SELECT p.project_id,p.name,p.type,p.status,p.praise_num,p.intro,s.src_name as project_img,s.path as project_path
				FROM project AS p LEFT JOIN project_task pt ON p.project_id = pt.project_id && pt.t_type = 3
					 LEFT JOIN src_relation sr ON pt.task_id = sr.relation_id && sr.type = 2
					 LEFT JOIN src s ON sr.src_id = s.src_id && s.type = 3
				ORDER BY p.praise_num DESC LIMIT 10';
		$res = Db::query( $sql );
		return $res;
		
	}
	
}

?>