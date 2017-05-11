<?php
namespace app\home\model;
use think\Model;
use think\Db;

class UserAttention extends Model{
	
	protected $table = 'user_attention';
	
	public function get_follow_users_by_id(){
		
		$follow_user_id = input('user_id');
		$sql = 'SELECT user_id
				FROM user_attention
				WHERE follow_user_id=:follow_user_id';
		
		$res = Db::query( $sql, ['follow_user_id' => $follow_user_id]);
		return $res;
		
	}
	
}

?>