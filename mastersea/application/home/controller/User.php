<?php
namespace app\home\controller;
use think\Controller;
use think\View;
use think\Db;
use think\Session;
use think\Config;
use think\Cache;

class User extends Controller{
	
	//缓存关系链
	public function get_appid_group_list(){
		$ret = [
			'r' => '-1',
			'msg' => ''
		];
		$user_tim  = new UserTim;
		$group_list = $user_tim->group_get_appid_group_list();
		if($group_list && $group_list['ActionStatus'] == 'OK' && $group_list['ErrorCode'] == 0){
			$group_id_list = $group_list['GroupIdList'];
			foreach($group_id_list as $v){
				$group_info = $user_tim->group_get_group_member_info($v['GroupId'], 10000, 0);
				if($group_info['ActionStatus'] == 'OK' && $group_info['ErrorCode'] == 0){
					unset($group_info['ActionStatus']);
					unset($group_info['ErrorCode']);
					cache( 'group_'.$v['GroupId'], json_encode($group_info), 0);
					$member_list = $group_info['MemberList'];
					foreach($member_list as $m){
						if( !cache('member_'.$m['Member_Account']) ){
							$friends_arr = [];
							$member_info = $user_tim->sns_friend_get_all( $m['Member_Account'] );
							if($member_info['ActionStatus'] == 'OK' && $member_info['ErrorCode'] == 0){
								$friends_arr['FriendNum'] = $member_info['FriendNum'];
								$friends_arr['InfoItem'] = $member_info['InfoItem'];
								cache( 'member_'.$m['Member_Account'], json_encode($friends_arr), 0);
							}
						}
					}
					$ret['r'] = 0;
				}
			}
		}else{
			$ret['msg'] = '获取群组为空';
		}
		return json($ret);
	}
	
}



?>