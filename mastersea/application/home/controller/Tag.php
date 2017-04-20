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
	public function getAll(){
		$result = [
			'r' => -1,
			'msg' => '',
			'data' => '',
		];
		$tag = new Tag;
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
		if($pid > 0){
			$res = Db::query('call addTag(:pid,:name)',['pid'=>$pid,'name'=>$name]);
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