<?php
if(isset($_GET['gID'])) {
	@require_once("../../../wp-config.php"); //setup wordpress
	$request = wp_remote_request("http://www.google.com/reader/public/atom/user%2F".$_GET['gID']."%2Fstate%2Fcom.google%2Fbroadcast?n=1");
	if($request['response']['code'] == "200") exit ("g1");
	exit("g0");
}

if (isset($_GET['Email']) && isset($_GET['Passwd'])) {
	$ch = curl_init("https://www.google.com/accounts/ClientLogin");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	
	$data = array('accountType' => 'GOOGLE',
	          'Email' => $_GET['Email'],
	          'Passwd' => $_GET['Passwd'],
	          'source'=>'wp-rec-reading-plugin',
	          'service'=>'reader');
	    
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	
	$hasil = curl_exec($ch);
	
	curl_close ($ch);
	
	$LSIDpos = strpos($hasil, "LSID");
	if(!$LSIDpos) {
		echo "0";	
		exit;
	}	
	
	$SID = trim(substr($hasil,4,$LSIDpos-4));
	
	$cookie = "SID=".$SID."; domain=.google.com; path=/; expires=1600000000";
	
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, 'http://www.google.com/reader/shared/');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt ($ch, CURLOPT_COOKIE, $cookie);
	$fullpage = curl_exec ($ch);
	curl_close ($ch);
	
	$userid_pos = strpos($fullpage,'_USER_ID = "');
	
	if(!$userid_pos) { 
		echo "Error finding user id. Plugin may be out of date.";
		exit;
	}
	
	$gr_userid = substr($fullpage,$userid_pos+12,20);
	
	echo $gr_userid;
}
?>
