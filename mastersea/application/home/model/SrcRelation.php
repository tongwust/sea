<?php
namespace app\home\model;
use think\Model;
use think\Db;

class SrcRelation extends Model{
	
	protected $table = 'src_relation';
	
	public function get_task_src_by_task_ids( $task_ids, $type){
		
		$sql = 'SELECT sr.task_id,s.src_id,s.src_name,s.src_order,s.src_type,s.status,s.path
				FROM src_relation sr LEFT JOIN src s ON sr.src_id = s.src_id
				WHERE sr.type = :type && sr.relation_id in (:task_ids) 
					GROUP BY sr.task_id';
		$res = Db::query( $sql, ['task_ids' => $task_ids,'type' => $type]);
		return $res;
		
	}
	
}
?>