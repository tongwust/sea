<?php
namespace app\home\model;
use think\Model;
use think\Db;

class UserTag extends Model{
	
	protected $table = 'user_tag';
	
	public function delete_user_tag( $pid, $themeid){
		$user_id = input('user_id');
		$sql = 'DELETE ut 
				FROM user_tag AS ut LEFT JOIN tag AS t ON ut.tag_id = t.tag_id
				WHERE ut.user_id = :user_id && t.pid = :pid && t.themeid = :themeid';
		$res = Db::query($sql , ['user_id'=> $user_id,'pid' => $pid, 'themeid' => $themeid]);
		return $res;
		
	}
	
	public function get_tag_by_userid( $pid, $themeid){
		$user_id = input('user_id');
		$sql = 'SELECT ti.tag_id,ti.name
				FROM user_tag AS ut LEFT JOIN tag AS t ON ut.tag_id = t.tag_id LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				WHERE ut.user_id = :user_id && t.pid = :pid && t.themeid = :themeid';
				
		$res = Db::query($sql , ['user_id' => $user_id,'pid' => $pid, 'themeid' => $themeid]);
		return $res;
	}
}



?>