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
		
		$sql = 'SELECT p.project_id,p.name,p.type,p.status,p.praise_num,p.like_num,p.intro,s.src_name as project_img,s.path as project_path
				FROM project AS p LEFT JOIN project_task pt ON p.project_id = pt.project_id && pt.t_type = 3
					 LEFT JOIN src_relation sr ON pt.task_id = sr.relation_id && sr.type = 2
					 LEFT JOIN src s ON sr.src_id = s.src_id && s.type = 3
				ORDER BY p.praise_num DESC LIMIT 0,10';
		$res = Db::query( $sql );
		return $res;
		
	}
	
//	public function updatePraiseNum( $opt ){
//		
////		$sql = 'UPDATE project 
////				SET praise_num = praise_num + 1
////				WHERE project_id = :project_id';
////		$res = Db::query( $sql, ['project_id' => input('cid')]);
//		$res = 0;
//		if( $opt == 1 ){
//			$res = Db::table('project')->where('project_id', input('cid'))->setInc('praise_num');
//		}else if( $opt == 2){
//			$res = Db::table('project')->where('project_id', input('cid'))-where('praise_num', '>', 0)->setDec('praise_num');
//		}
//		return $res;
//	}
//	public function updateIncCollectNum(){
//		$res = 0;
//		if( $opt == 1){
//			$res = Db::table('project')->where('project_id', input('cid'))->setInc('collect_num');
//		}else if( $opt == 2){
//			$res = Db::table('project')->where('project_id', input('cid'))->where('collect_num', '>', 0)->setDec('collect_num');
//		}
//		return $res;
//	}
}

?>