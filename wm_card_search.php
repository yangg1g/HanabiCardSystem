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
function wm_cardsearch($uid){
	$DB = DB::object();
	$data = null;
	//$uid = "103ee840a98a6da247cf2f7b09e3cb58";
	if(!preg_match("/^[A-Za-z0-9]+$/",$uid)){
		$data = json_encode(array('code'=>"0"));
	}else{
		$comment_author_uid = "\"".$uid."\"";
		$mgid=$DB->query("SELECT * FROM pre_common_wm_card WHERE uid=".$comment_author_uid."");
		$mgidinfo=$DB->fetch_array($mgid);
		if ($mgidinfo) {
			$json_string = json_decode(file_get_contents('cardData.json'), true);//查询卡牌数据
			$data = json_encode(array('code'=>"202",'data'=>$mgidinfo['cardID'],'cardCount'=>$mgidinfo['cardCount'],'score'=>$mgidinfo['score'],'level'=>$mgidinfo['level'],'cardLength'=>count($json_string['cardData'])));
		}else{
			$data = json_encode(array('code'=>"1"));
		}
	}
	//0为字符串有误,1为没数据
	echo $data;
}
if(isset($_POST['uid']) && $_POST['uid']!="")
	wm_cardsearch($_POST['uid']);
else
	wm_cardsearch(md5($_G['uid']));
?>