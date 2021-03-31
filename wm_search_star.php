<?php
require_once('../../../init.php');	
function wm_star_search(){
	$DB = Database::getInstance();
	$data = null;
	$uid = strip_tags($_POST['uid']);
	{
		$comment_author_uid = "\"".$uid."\"";
		$mgid=$DB->query("SELECT * FROM ".DB_PREFIX."wm_card WHERE uid=".$comment_author_uid."");
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
wm_star_search();
?>