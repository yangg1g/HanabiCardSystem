<?php
require_once('../../../source/class/class_core.php');	
require_once('../../../source/function/function_home.php');	
require_once('module.php');
// error_reporting(0);
$discuz = C::app();

$cachelist = array('magic','usergroups', 'diytemplatenamehome');
$discuz->cachelist = $cachelist;
$discuz->init();

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
function wm_star_search($uid){
	$DB = DB::object();
	$data = null;
	{
		$comment_author_uid = "\"".$uid."\"";
		$mgid=$DB->query("SELECT * FROM pre_common_wm_card WHERE uid=".$comment_author_uid."");
		$mgidinfo=$DB->fetch_array($mgid);
		if ($mgidinfo) {
			$data = json_encode(array('code'=>"202",'star'=>$mgidinfo['starCount']));
		}else{
			$data = json_encode(array('code'=>"1"));
		}
	}
	//0为字符串有误,1为没数据
	echo $data;
}
wm_star_search(md5($_G['uid']));
?>