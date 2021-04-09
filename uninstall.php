<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function callback_rm(){
	$wmCard_set=unserialize(ltrim(file_get_contents(dirname(__FILE__).'/wm_card.com.php'),'<?php die; ?>'));
	if(intval($wmCard_set['delDatabase'])=='1'){
		$DB = DB::object();
		$query = $DB->query("DROP TABLE IF EXISTS pre_common_wm_card");
	}
}
callback_rm();
?>
