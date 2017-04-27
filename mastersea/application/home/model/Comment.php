<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Comment extends Model{
	
	protected $table = 'comment';
	
	public function get_task_comment_by_task_ids( $task_ids, $type){
		
		$sql = 'SELECT * 
				FROM comment 
				WHERE type = :type && cid IN (:task_ids)
				GROUP BY cid ORDER BY create_time ASC';
		$res = Db::query( $sql, ['task_ids' => $task_ids,'type' => $type]);
		return $res;
	}
	
}
?>