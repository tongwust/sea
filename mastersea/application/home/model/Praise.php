<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Praise extends Model{
	
	protected $table = 'praise';
	
	public function add_praise(){
		
		$sql = 'INSERT INTO praise( cid, type, user_id) VALUES( :cid, :type, :user_id)';
		$res = Db::query( $sql, ['cid' => input('cid'), 'type' => input('type'), 'user_id' => input('user_id')]);
		
		return $res;
	}
	
	public function del_praise(){
		
		$sql = 'DELETE FROM praise WHERE cid = :cid && user_id = :user_id && type = :type';
		$res = Db::query( $sql, ['cid' => input('cid'), 'user_id' => input('user_id'), 'type' => input('type')]);
		
		return $res;
	}
	
}


?>