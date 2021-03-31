<?php
require_once('../../../init.php');	
function wm_cardsearch(){
	$DB = Database::getInstance();
	$data = null;
	$uid = strip_tags($_POST['uid']);
	//$uid = "103ee840a98a6da247cf2f7b09e3cb58";
	if(!preg_match("/^[A-Za-z0-9]+$/",$uid)){
		$data = json_encode(array('code'=>"0"));
	}else{
		$comment_author_uid = "\"".$uid."\"";
		$mgid=$DB->query("SELECT * FROM ".DB_PREFIX."wm_card WHERE uid=".$comment_author_uid."");
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
wm_cardsearch();
?>