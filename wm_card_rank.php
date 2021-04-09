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
function searchWmRank(){
    $wmRankJsonData = null;
    if(file_exists('cardRank.json')){//判断json文件是否存在
        $wmRankJsonData = json_decode(file_get_contents('cardRank.json'),true);
        $updataTime = $wmRankJsonData['updataTime'];
        $NewtimeStamp = time();
        if($NewtimeStamp - $updataTime > 259200){
            //已经三天没更新了
            $wmRankJsonData = searchWmDataBaseByRank();
        }
    }else{
        $wmRankJsonData = searchWmDataBaseByRank();
    }
    echo json_encode($wmRankJsonData);
}
function searchWmDataBaseByRank(){
    $DB = DB::object();
    $mgidScore=$DB->query("
        SELECT *
        FROM `pre_common_wm_card`
        ORDER BY `pre_common_wm_card`.`score` DESC
        LIMIT 0 , 10
    ");
    $mgidCardLength=$DB->query("
        SELECT *
        FROM `pre_common_wm_card`
        ORDER BY LENGTH(cardID) DESC
        LIMIT 0 , 10
    ");
    $mgidLevel=$DB->query("
        SELECT *
        FROM `pre_common_wm_card`
        ORDER BY `pre_common_wm_card`.`level` DESC
        LIMIT 0 , 10
    ");
    $mgidDeminingStarCount=$DB->query("
        SELECT *
        FROM `pre_common_wm_card`
        ORDER BY `pre_common_wm_card`.`deminingStarCount` DESC
        LIMIT 0 , 10
    ");
    $wmRankScoreArr = array();
    $wmRankCardArr = array();
    $wmRankLevel = array();
    $wmRankDeminingStarCount = array();
    $timeStamp = time();
    while($result=$DB->fetch_array($mgidScore)){
        array_push($wmRankScoreArr,array('score'=>$result['score'],'uid'=>$result['uid']));
    }
    while($result=$DB->fetch_array($mgidCardLength)){
        array_push($wmRankCardArr,array('cardID'=>$result['cardID'],'uid'=>$result['uid']));
    }
    while($result=$DB->fetch_array($mgidLevel)){
        array_push($wmRankLevel,array('level'=>$result['level'],'uid'=>$result['uid']));
    }
    while($result=$DB->fetch_array($mgidDeminingStarCount)){
        array_push($wmRankDeminingStarCount,array('deminingStarCount'=>$result['deminingStarCount'],'uid'=>$result['uid']));
    }
    $wmRankJsonData = array('score'=>$wmRankScoreArr,'card'=>$wmRankCardArr,'level'=>$wmRankLevel,'deminingStarCount'=>$wmRankDeminingStarCount,'updataTime'=>$timeStamp);
    file_put_contents('cardRank.json', json_encode($wmRankJsonData));
    return $wmRankJsonData;
};
searchWmRank();
?>
