<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;

class Project extends Controller{
	public function index(){
		
	}
	public function add(){
		$name = trim(input('name'));
		$type = input('type');
		$en_name = trim(input('en_name'));
		$cat_name = trim(input('cat_name'));
		$address = trim(input('address'));
		$project_start_time = input('project_start_time');
		$project_end_time = input('project_end_time');
		$intro = trim(input('intro'));
		$status = 0;//待审
		$create_time = now();
		
		
	}
	
}



?>