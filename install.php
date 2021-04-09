<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function callback_init(){
	$DB = DB::object();
	$check_table_exist = $DB->query('SHOW TABLES LIKE "pre_common_wm_card"');
	if($DB->num_rows($check_table_exist) == 0){// 新建数据表
		$dbcharset = 'utf8mb4';
		$type = 'MYISAM';
		$add = "ENGINE=".$type." DEFAULT CHARSET=".$dbcharset.";";
		$sql = "CREATE TABLE  `pre_common_wm_card` (
		`id` mediumint(8) unsigned NOT NULL auto_increment,
		`uid` varchar(255) NOT NULL default '0',
		`cardID` longtext NOT NULL,
		`cardCount` longtext NOT NULL,
		`timeStamp` bigint(20) NOT NULL,
		`todayCount` int(10) NOT NULL,
		`score` int(10) NOT NULL default '0',
		`level` int(10) NOT NULL default '0',
		`exp` int(10) NOT NULL default '0',
		`battleStamp` bigint(20) NOT NULL,
		`exData` longtext NOT NULL,
		`starCount` bigint(20) NOT NULL default '0',
		`verifyCode` int(10) NOT NULL default '0',
		`verifyCodeRemember` int(10) NOT NULL default '0',
		`verifyCodeStamp` bigint(20) NOT NULL,
		`verifyCodeCount` int(10) NOT NULL,
		`deminingStamp` bigint(20) NOT NULL,
		`deminingStarCount` bigint(20) NOT NULL default '0',
		`bouerse` longtext NOT NULL,
		`guessCard` longtext NOT NULL,
		PRIMARY KEY  (`id`)
)".$add;
		$DB->query($sql);
	}
}

callback_init();
$finish = TRUE;
?>
