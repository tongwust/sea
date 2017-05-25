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
	
	public function getProjectAttenNum($project_ids_str){
		
		$sql = 'SELECT project_id,count(user_id) as atten_num
				FROM project_attention
				WHERE project_id in ('.$project_ids_str.')
				GROUP BY project_id';
		$res = Db::query( $sql );
		return $res;
	}
}
?>