<?php
namespace app\home\model;
use think\Model;
use think\Db;


class Collect extends Model{
	
	protected $table = 'collect';
	
//	public function add_collect(){
//		
//		$sql = 'INSERT INTO collect( cid, type, user_id) VALUES( :cid, :type, :user_id)';
//		$res = Db::query( $sql, ['cid' => input('cid'), 'type' => input('type'), 'user_id' => input('user_id')]);
//		
//		return $res;
//	}
//
//	public function del_collect(){
//		
//		$sql = 'DELETE FROM collect WHERE cid = :cid && user_id = :user_id && type = :type';
//		$res = Db::query( $sql, ['cid' => input('cid'), 'user_id' => input('user_id'), 'type' => input('type')]);
//		
//		return $res;
//	}
}

?>