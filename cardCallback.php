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

function wm_cardWrite($uid){
	$DB = DB::object();
	$data = null;
	// $uid = "2343";
	$choiseIndex = intval(strip_tags($_POST['choiseIndex']));
	// $choiseIndex = 0;
	if(isset($uid) && $uid!=""){
		{//用正则表达式函数进行判断  
           //uid正确
			{
				$comment_author_uid = "\"".md5($uid)."\"";
				$mgid=$DB->query("SELECT * FROM pre_common_wm_card WHERE uid=".$comment_author_uid."");
				
				$mgidinfo=$DB->fetch_array($mgid);
				$timeStamp = time();
				$wmoriginDate = null;
				$wmnowDate_ = null;
				$originToday = null;
				$DateCheck = true;
				$wmCard_set=unserialize(ltrim(file_get_contents(dirname(__FILE__).'/wm_card.com.php'),'<?php die; ?>'));
				//次数的基数（次数-1）
				$canGetCardChance = intval($wmCard_set['chance'])-1;
				if($canGetCardChance<0){
					$canGetCardChance = 0;
				}
				//根据竞技分数增加抽卡次数
				$canGetCardChancePlus = floor($mgidinfo['score']/1000);
				if($canGetCardChancePlus>5){//加成最多5次
					$canGetCardChancePlus = 5;
				}
				$leftGetChance = $canGetCardChance;
				if ($mgidinfo) {
					$wmoriginTime = intval ($mgidinfo['timeStamp']);
					$wmnowDate_ = date("Ymd", $timeStamp);
					$wmoriginDate =  date("Ymd", $wmoriginTime);
					if($wmoriginDate==$wmnowDate_){
						//判断今天抽了几次
						//获取抽卡次数
						$originToday = $mgidinfo['todayCount'];
						$canGetCardChance = $canGetCardChance + $canGetCardChancePlus;
						$leftGetChance = $canGetCardChance - $originToday; 
						if($leftGetChance<0){
							$DateCheck = false;
						}
					}else{
						$leftGetChance = $canGetCardChance+$canGetCardChancePlus;
						$query = "Update pre_common_wm_card set todayCount=0 where uid=".$comment_author_uid."";
						$result=$DB->query($query);
					}
				}
				if($DateCheck){
					//正常抽
					$randomCardID = null;
					$cardChoiseList = array();
					$testCount = 0;
					while (count($cardChoiseList)<3 && $testCount<100) {
						$randomCardR = mt_rand(1, 100);
						$randomCardIDArrContent = wmCreatCardId($randomCardR);
						array_push($cardChoiseList,$randomCardIDArrContent);
						$testCount = $testCount +1;
						$cardChoiseList = array_values(array_unique($cardChoiseList,SORT_REGULAR));
					}

					if(!($choiseIndex>=0&&$choiseIndex<=2)){
						$choiseIndex = 1;
					}

					$randomCardID = $cardChoiseList[$choiseIndex];

					if(empty($randomCardID)){//防止抽到空牌
						//空牌
						$data = json_encode(array('code'=>"4"));
					}else{
						$json_string = json_decode(file_get_contents('cardData.json'), true);//查询卡牌数据
						$getCardData = $json_string['cardData'][$randomCardID];//抽中卡牌数据
						$cardJsonData = array('mailMD5'=>md5($uid),'cardInfo'=>$getCardData,'cardID'=>$randomCardID,'massageType'=>'dailyCard');
						//写入或更新最新抽奖列表json
						wmWriteJson($cardJsonData);
						//判断数据库是否存在这个用户的抽奖信息
						if (!$mgidinfo) {
							$sqli="INSERT INTO pre_common_wm_card (uid,cardID,cardCount,timeStamp,todayCount,score,level,exp,battleStamp,exData,starCount,verifyCode,verifyCodeStamp,verifyCodeCount,bouerse,guessCard) VALUES(".$comment_author_uid.",'".$randomCardID."','1',".$timeStamp.",1,0,0,0,".$timeStamp.",'',1250,0,0,0,'','')";
							$DB->query($sqli);
						}else{
							$originCarID = $mgidinfo['cardID'];
							$originCardCount = $mgidinfo['cardCount'];
							//循环遍历卡组
							$callBackCardInfo = wmAddCard($originCarID,$originCardCount,$randomCardID);

							$query = "Update pre_common_wm_card set cardID='".$callBackCardInfo['originCarIDText']."' , cardCount='".$callBackCardInfo['originCardCountText']."' , timeStamp=".$timeStamp." , todayCount=todayCount+1 where uid=".$comment_author_uid."";
							$result=$DB->query($query);
						}
						$data = json_encode(array('code'=>"202",'card'=>$randomCardID,'uidmd5'=>md5($uid),'todaycount'=>$originToday,'cardChoiseList'=>$cardChoiseList,'choiseIndex'=>$choiseIndex,'leftGetChance'=>$leftGetChance));
					}
				}else{
					$data = json_encode(array('code'=>"2" , 'data'=>$wmoriginDate , 'datanow'=>$wmnowDate_,'uidmd5'=>md5($uid)));  
				}
			}
		}
	}else{
		//uid为空
		$data = json_encode(array('code'=>"0")); 
	}
	//0为uid为空，1为uid不合格，2为今天已经抽过了，3为评论表里找不到uid，4为空牌，202为正常输出
	echo $data;
}
wm_cardWrite($_G['uid']);
?>