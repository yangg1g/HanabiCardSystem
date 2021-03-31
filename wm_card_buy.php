<?php
require_once('../../../init.php');
require_once('module.php');
function buyCard(){
    $uid = strip_tags($_POST['uid']);
    //$uid = "123";
    $buyType = intval($_POST['type']);
    //$buyType = 4;
    $data = null;
    if(isset($uid) && $uid!=""){
        {//用正则表达式函数进行判断 
            $DB = Database::getInstance();
            $uidMD5 = "\"".md5($uid)."\"";
            $mgid=$DB->query("SELECT * FROM ".DB_PREFIX."wm_card WHERE uid=".$uidMD5."");
            $mgidinfo=$DB->fetch_array($mgid);
            if ($mgidinfo) {
                {
                    $timeStamp = time();
                    $verifyCodeRemember = intval($mgidinfo['verifyCodeRemember']);
                    {
                        $randomCardRate = mt_rand(1, 100);
                        $starFlag = false;//true为星星不足
                        $starCount = intval($mgidinfo['starCount']);
                        $shouldStar = 9999;
                        $buyClass = 0;//哪一类型的商品，1为指定抽卡、2为连抽、3为CD相关商品
                        $chinChioseCardNum = 0;//连抽几次
                        //如果保存密码的话则verifyCodeRemember为1
                        $verifyCodeRemember = 0;
                        if($buyType==3){
                            $shouldStar = 30;
                            $buyClass = 1;
                            if($starCount<$shouldStar){
                                $starFlag = true;
                            }
                        }else if($buyType==4){
                            $randomCardRate = 65;
                            $shouldStar = 90;
                            $buyClass = 1;
                            if($starCount<$shouldStar){
                                $starFlag = true;
                            }
                        }else if($buyType==5){
                            $randomCardRate = 87;
                            $shouldStar = 200;
                            $buyClass = 1;
                            if($starCount<$shouldStar){
                                $starFlag = true;
                            }
                        }else if($buyType==6){
                            $randomCardRate = 100;
                            $shouldStar = 600;
                            $buyClass = 1;
                            if($starCount<$shouldStar){
                                $starFlag = true;
                            }
                        }else if($buyType==1001){
                            $chinChioseCardNum = 10;
                            $shouldStar = 270;
                            $buyClass = 2;
                            if($starCount<$shouldStar){
                                $starFlag = true;
                            }
                        }
                        else if($buyType==1002){
                            $chinChioseCardNum = 30;
                            $shouldStar = 780;
                            $buyClass = 2;
                            if($starCount<$shouldStar){
                                $starFlag = true;
                            }
                        }
                        else if($buyType==1003){
                            $chinChioseCardNum = 50;
                            $shouldStar = 1250;
                            $buyClass = 2;
                            if($starCount<$shouldStar){
                                $starFlag = true;
                            }
                        }else{
                            $randomCardRate = -1;
                        }
                        if(!$starFlag&&$buyClass==1){
                            //正常抽
                            $randomCardID = wmCreatCardId($randomCardRate);
                            $originCarID = $mgidinfo['cardID'];
                            $originCardCount = $mgidinfo['cardCount'];

                            //循环遍历卡组
                            $callBackCardInfo = wmAddCard($originCarID,$originCardCount,$randomCardID);
                            $originCarIDText = $callBackCardInfo['originCarIDText'];
                            $originCardCountText = $callBackCardInfo['originCardCountText'];

                            $starCountAfter = $starCount - $shouldStar;
                            if($starCountAfter<0){
                                $starCountAfter = 0;
                            }
                            $query = "Update ".DB_PREFIX."wm_card set verifyCodeRemember='".$verifyCodeRemember."' , cardID='".$originCarIDText."' , cardCount='".$originCardCountText."' , starCount=".$starCountAfter." where uid=".$uidMD5."";
                            $result=$DB->query($query);

                            $json_string = json_decode(file_get_contents('cardData.json'), true);//查询卡牌数据
                            
                            $getCardData = $json_string['cardData'][$randomCardID];//抽中卡牌数据
                            $cardJsonData = array('mailMD5'=>md5($uid),'cardInfo'=>$getCardData,'cardID'=>$randomCardID,'useStar'=>$shouldStar,'massageType'=>'buy','buyClass'=>$buyClass);
                            wmWriteJson($cardJsonData);

                            $data = json_encode(array('code'=>"202" , 'card'=>$randomCardID ,'starCountAfter'=>$starCountAfter ,'buyClass'=>$buyClass)); 

                        }else if(!$starFlag&&$buyClass==2){
                            //多连抽
                            $originCarID = $mgidinfo['cardID'];
                            $originCardCount = $mgidinfo['cardCount'];
                            $chainChioseCardId = array();
                            for($i=0;$i<$chinChioseCardNum;$i++){
                                $randomCardRate = mt_rand(1, 100);
                                $randomCardID = wmCreatCardId($randomCardRate);
                                array_push($chainChioseCardId,$randomCardID);
                                //循环遍历卡组
                                $callBackCardInfo = wmAddCard($originCarID,$originCardCount,$randomCardID);
                                $originCarID = $callBackCardInfo['originCarIDText'];
                                $originCardCount = $callBackCardInfo['originCardCountText'];
                            }
                            $cardJsonData = array('mailMD5'=>md5($uid),'chinChioseCardNum'=>$chinChioseCardNum,'card'=>$chainChioseCardId,'useStar'=>$shouldStar,'massageType'=>'buy','buyClass'=>$buyClass);
                            wmWriteJson($cardJsonData);
                            $starCountAfter = $starCount - $shouldStar;
                            if($starCountAfter<0){
                                $starCountAfter = 0;
                            }
                            $query = "Update ".DB_PREFIX."wm_card set verifyCodeRemember='".$verifyCodeRemember."' , cardID='".$originCarID."' , cardCount='".$originCardCount."' , starCount=".$starCountAfter." where uid=".$uidMD5."";
                            $result=$DB->query($query);
                            $data = json_encode(array('code'=>"202" , 'card'=>$chainChioseCardId ,'starCountAfter'=>$starCountAfter ,'buyClass'=>$buyClass));

                        }else if($buyClass!=0&&$starFlag){
                            //星星不够
                            $data = json_encode(array('code'=>"5",'starCount'=>$starCount,'shouldStar'=>$shouldStar));  
                        }else{
                        //type有误
                            $data = json_encode(array('code'=>"4"));  
                        }
                    }
                }
            }else{
                //没有该用户
                $data = json_encode(array('code'=>"6")); 
            }
        }

    }else{
        //uid为空
		$data = json_encode(array('code'=>"0")); 
    }
    echo $data;
}
//if(isset($_POST['uid'])&&isset($_POST['type'])){
    buyCard();
//}

?>