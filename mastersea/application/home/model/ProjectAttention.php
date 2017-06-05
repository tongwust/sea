<?php
namespace app\home\model;
use think\Model;
use think\Db;

class ProjectAttention extends Model{
	
	protected $table = 'project_attention';
	
	public function add(){
		
		$sql = 'INSERT INTO project_attention(project_id, user_id, relation_type) VALUES(:project_id, :user_id, :relation_type)';
		
		$res = Db::query( $sql, ['project_id' => input('project_id'), 
								 'user_id' => input('user_id'),
								 'relation_type' => (empty(input('relation_type'))?1:input('relation_type'))
								 ]);
		return $res;
	}
	public function getMyAttenProjectNum(){
		$user_id = input('user_id');
		$sql = 'SELECT user_id,count(project_id) AS my_atten_pnum
				FROM project_attention
				WHERE user_id = :user_id && relation_type = 1
					GROUP BY user_id';
		$res = Db::query( $sql, ['user_id' => $user_id] );
		
		return $res;
	}
	public function getProjectAttenNum($project_ids_str){
		
		$sql = 'SELECT project_id,count(user_id) as atten_num
				FROM project_attention
				WHERE project_id in ('.$project_ids_str.')
				GROUP BY project_id';
		$res = Db::query( $sql );
		return $res;
	}
	
	public function getProjectAttenNumByProjectId(){
		$project_id = input('project_id');
		$sql = 'SELECT count(user_id) as atten_num
				FROM project_attention
				WHERE project_id = :project_id';
		$res = Db::query( $sql, ['project_id' => $project_id]);
		return $res;
	}
	public function myAttenProjectTasklist( $from, $page_size){
		$sql = 'SELECT pa.project_id,t.task_id,t.title,t.description,t.praise_num,t.collect_num,t.create_time,
					   s.src_id,s.src_name,s.src_order,s.type src_type,s.path,s.access_url,s.resource_path
				FROM project_attention AS pa LEFT JOIN project AS p ON pa.project_id = p.project_id && p.status != -1
					LEFT JOIN project_task_user AS ptu ON ptu.project_id = p.project_id
					LEFT JOIN task AS t ON ptu.task_id = t.task_id && t.status != -1
					LEFT JOIN src_relation AS sr ON t.task_id = sr.relation_id && sr.type = 2
					LEFT JOIN src AS s ON sr.src_id = s.src_id
				WHERE pa.user_id = :user_id
						LIMIT :from,:page_size';
		$res = Db::query( $sql, ['user_id' => input('user_id'),'from'=>$from,'page_size'=>$page_size]);
		
		return $res;
	}
	
}
?>