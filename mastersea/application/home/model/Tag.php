<?php
namespace app\home\model;
use think\Model;
use think\Db;

class Tag extends Model{
	protected $table = 'user';
	
	public function selectAll(){
		$sql = 'SELECT t.tag_id,concat(repeat("-",t.level),ti.name),t.themeid,t.lft,t.rgt
				FROM tag AS t LEFT JOIN tag_info AS ti ON t.tag_id = ti.tag_id
				WHERE themeid = 1
				ORDER BY t.lft';
		$res = Db::query($sql);
    	return count($res);
	}
}
?>