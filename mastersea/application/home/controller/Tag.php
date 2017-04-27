<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Tag extends Controller{
	
	public function index(){
		
	}
	public function get_part_by_tagid_themeid(){
		$ret = [
			'r' => -1,
			'msg' => '',
			'data' => '',
		];
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		if($tag_id > 0 && $themeid > 0){
			
			$tag = model('Tag');
			$ret['data'] = $tag->get_tag_by_themeid();
			$ret['r'] = 0;
		}else{
			$ret['msg'] = '传入参数不合法';	
		}
		return json($ret);
	}
	
	public function get_dim_name_by_tagid(){
		$ret = [
			'r' => -1,
			'msg' => '',
			'data' => '',
		];
		$tag_id = input('tag_id');
		$themeid = input('themeid');
		$part = trim(input('part'));
		if($tag_id > 0 && $themeid > 0 && strlen($part) > 0){
			$tag = model('Tag');
			$ret['data'] = $tag->get_dim_tag_by_pid_themeid();
			$ret['r'] = 0;
		}else{
			$ret['msg'] = '传入参数不合法';
		}
		return json($ret);
	}
	
	public function getAll(){
		$result = [
			'r' => 0,
			'msg' => '',
			'data' => '',
		];
		$tag = model('Tag');
		$res = $tag->selectAll();
		$result['data'] = $res;
		return json($result);
	}
	public function add(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$pid = input('pid');
		$name = input('name');
		$themeid = empty(input('themeid'))?1:input('themeid');
		if($pid > 0){
			$res = Db::query('call addTag(:pid,:name,:themeid)',['pid'=>$pid,'name'=>$name,'themeid'=>$themeid]);
			if(count($res) > 0 && $res[0][0]['result'] == 1000){
				$result['r'] = 0;
				$result['msg']  = '添加成功';
			}else{
				$result['msg'] = '添加失败';
			}
		}else{
			$result['msg'] = 'pid 参数不符合要求';
		}
		return json($result);
		exit;
	}
	public function del(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$pid = input('pid');
		if($pid > 0){
			$res = Db::query('call delTag(:pid)',['pid'=>$pid]);
			if(count($res) > 0 && $res[0][0]['result'] == 1000){
				$result['r'] = 0;
				$result['msg'] = '删除成功';
			}else{
				$result['msg'] = '删除失败';
			}
		}else{
			$result['msg'] = 'pid 参数不符合要求';
		}
		return json($result);
		exit;
	}
	public function move(){
		$result = [
			'r' => -1,
			'msg' => '',
		];
		$pid = input('pid');
		$tid = input('tid');
		if( $pid > 0 && $tid > 0){
			$res = Db::query('call moveTag(:pid,:tid)',['pid'=>$pid,'tid'=>$tid]);
			if(count($res) > 0 && $res[0][0]['result'] == 1000){
				$result['r'] = 0;
				$result['msg'] = '移动成功';
			}else{
				$result['msg'] = '移动失败';
			}
		}else{
			$result['msg'] = 'pid 参数不符合要求';
		}
		return json($result);
		exit;
	}
}






?>