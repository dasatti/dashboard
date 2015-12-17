<?php
define('ROOT',"../");
include(ROOT.'../adodb/adodb.inc.php');
include('../../include/siteconfig.inc.php');
$db = ADONewConnection('mysql');
$db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");
/*
	Sample Processing of Forgot password form via ajax
	Page: extra-register.html
*/

# Response Data Array
$resp = array();

// Fields Submitted
$username = $_POST["username"];
$password = $_POST["password"];

// This array of data is returned for demo purpose, see assets/js/neon-forgotpassword.js
$resp['submitted_data'] = $_POST;

// Login success or invalid login data [success|invalid]
// Your code will decide if username and password are correct
$login_status = 'invalid';

$sql = "SELECT * FROM ".$tblprefix."admin WHERE username = '".mysql_escape_string($username)."'
    AND password = '".mysql_escape_string($password)."'";
$rs = $db->Execute($sql);
$isrs = $rs->RecordCount();

if($isrs >0){
	$login_status = 'success';
	$_SESSION['lm_auth']['islogin'] = 'yes';
	$_SESSION['lm_auth']['islogin'] = true;
	$_SESSION['lm_auth']['name'] = $rs->fields['name'];
	$_SESSION['lm_auth']['email'] = $rs->fields['email'];
	$_SESSION['lm_auth']['noreplyemail'] = $rs->fields['noreplyemail'];
	$_SESSION['lm_auth']['notifyemail'] = $rs->fields['notifyemail'];
	$_SESSION['lm_auth']['type'] = $rs->fields['account_type'];
	
}else{
	$login_status = 'invalid';
}
		
$resp['login_status'] = $login_status;

// Login Success URL
if($login_status == 'success')
{
	// If you validate the user you may set the user cookies/sessions here
		#setcookie("logged_in", "user_id");
		#$_SESSION["logged_user"] = "user_id";
	
	// Set the redirect url after successful login
	//if($rs->fields['account_type']=='admin'){
		//$resp['redirect_url'] = 'admin.php?act=dashboard';
	//}else{
		$resp['redirect_url'] = 'admin.php?act=managecalls';
	//}
}
echo json_encode($resp);