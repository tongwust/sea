<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Comment extends Controller{
	
	public function index(){
		
	}
	
	public function add(){
		$ret = [
			'r' => 0,
			'msg' => '',
		];
		$pid = input('pid');
		$cid = input('cid');
		$type = input('type');
		$by_user_id = input('by_user_id');
		$user_id = input('user_id');
		$content = trim(input('content'));
		
		$comment = model('Comment');
		if($user_id > 0 && strlen($content) > 0){
			$comment->pid = $pid;
			$comment->cid = $cid;
			$comment->type = $type;
			$comment->by_user_id = $by_user_id;
			$comment->user_id = $user_id;
			$comment->content = $content;
			$comment->create_time = now();
			
			$comment->save();
			$ret['pc_id'] = $comment->pc_id;
			$ret['msg'] = '添加成功';
		}else{
			$ret['r'] = -1;
			$ret['msg'] = 'user_id非法或评论内容不能空';
		}
		return json($ret);
	}
	
	
	
}


?>