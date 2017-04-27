<?php
namespace app\home\model;
use think\Model;
use think\Db;

class ProjectTag extends Model{
	
	protected $table = 'user_project_tag';
	
	public function get_tag_by_userid_projectid(){
		$project_id = input('project_id');
		$user_id = input('user_id');
		$sql = 'SELECT upt.tag_id,ti.tag_name
				FROM user_project_tag AS upt LEFT JOIN tag_info AS ti ON upt.tag_id = ti.tag_id
				WHERE upt.user_id = :user_id && upt.project_id = :project_id';
		$res = Db::query( $sql, ['user_id' => $user_id,'project_id' => $project_id]);
		return $res;
	}
	
}
?>