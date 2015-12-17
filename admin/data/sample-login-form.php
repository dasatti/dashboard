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

$sql = "SELECT * FROM ".$tblprefix."admin WHERE username = '".mysql_escape_string($username)."' AND
										password = '".mysql_escape_string($password)."'";
$rs = $db->Execute($sql);
$isrs = $rs->RecordCount();

if($isrs >0){
	$login_status = 'success';
	$_SESSION['lm_auth']['islogin'] = 'yes';
	$_SESSION['lm_auth']['islogin'] = true;
	$_SESSION['lm_auth']['id'] = $rs->fields['id'];
	$_SESSION['lm_auth']['name'] = $rs->fields['name'];
	$_SESSION['lm_auth']['image'] = $rs->fields['image'];
	$_SESSION['lm_auth']['email'] = $rs->fields['email'];
	$_SESSION['lm_auth']['noreplyemail'] = $rs->fields['noreplyemail'];
	$_SESSION['lm_auth']['notifyemail'] = $rs->fields['notifyemail'];
	$_SESSION['lm_auth']['type'] = $rs->fields['account_type'];
	$_SESSION['lm_auth']['client_id'] = $rs->fields['client_id'];
	$_SESSION['lm_auth']['table_id'] = $rs->fields['id'];
	$_SESSION['lm_auth']['account_type'] = $rs->fields['account_type'];
	$_SESSION['lm_auth']['campaign_start'] = $rs->fields['campaign_start'];
	$_SESSION['lm_auth']['campaign_end'] = $rs->fields['campaign_end'];
	$_SESSION['lm_auth']['ga_view_id'] = $rs->fields['ga_view_id'];
	
}else{
	$login_status = 'invalid';
}
		
$resp['login_status'] = $login_status;

// Login Success URL
if($login_status == 'success')
{



	$url = "https://api.unbounce.com/pages/".$rs->fields['unbounce_id']."/leads";
	$username = '5e319884847c030a1e83707ba7af5126';
	$password = '';
	
	$process = curl_init();
	curl_setopt($process, CURLOPT_URL, $url); 
	curl_setopt($process, CURLOPT_HEADER, 1);                                                                           
	curl_setopt($process, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Content-Type: application/xml'));        
	curl_setopt($process, CURLOPT_USERPWD, $username . ":" . $password);                                                
	curl_setopt($process, CURLOPT_TIMEOUT, 30);                                                                         
	curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);                                                                
	curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
	
	$response = curl_exec($process);  
	curl_close($process);
	
	$arr_json = explode("Connection: keep-alive",$response);
	$arr_php = json_decode($arr_json[1], true);
	//echo "<pre>";
	//print_r($arr_php['leads']);exit;
	
	if($rs->fields['unbounce_id']=='d65bbb90-8124-11e4-a2c1-22000a91bbb2'){
		//www.highqualitycoffeecapsules.com/
		$namefield = 'name';
		$emailfield = 'emailAddress';
		$phonefield = 'contactNumber';
		$messagefield = 'howWeMayAssistYou';
		
	}elseif($rs->fields['unbounce_id']=='e85e7926-9642-11e4-b1fb-22000b252516'){
		//www.beverlyhillsuae.com/
		$namefield = 'fullName';
		$emailfield = 'email';
		$phonefield = 'phoneNumber';
		$messagefield = 'message';
		
	}elseif($rs->fields['unbounce_id']=='3f03cf50-8a8c-11e4-8081-22000b380175'){
		//www.braces-dubai.com/
		$namefield = 'name';
		$emailfield = 'email';
		$phonefield = 'phoneNo';
		$messagefield = 'message';
		
	}elseif($rs->fields['unbounce_id']=='638e30b4-53a9-11e4-b12f-22000b300054'){
		//www.rentacar-dubai.com/
		$namefield = 'yourName';
		$emailfield = 'yourEmail';
		$phonefield = 'phoneNumber';
		$messagefield = 'message';
		
	}else{
		$namefield = 'name';
		$emailfield = 'email';
		$phonefield = 'phoneNumber';
		$messagefield = 'message';
	}
	foreach($arr_php['leads'] as $arr_single){
		$name	  = $arr_single['formData'][$namefield][0];
		$email	  = $arr_single['formData'][$emailfield][0];
		$phone	  = $arr_single['formData'][$phonefield][0];
		$message  = $arr_single['formData'][$messagefield][0];
		$requestId  = $arr_single['extraData']['requestId'];
		/*$createdAt  = $arr_single['createdAt'];
		$createdAt_arr = explode("T",$createdAt);*/
		$createdAt  = $arr_single['createdAt'];
		$createdAt_arr = explode("T",$createdAt);
		$createdAt_date = $createdAt_arr[0];
		$createdAt_time_arr = explode('+',$createdAt_arr[1]);
		$createdAt_time = $createdAt_time_arr[0];
		
		$createdAt_db = date('Y-m-d H:i:s',strtotime($createdAt_date.' '.$createdAt_time));
		
		$sql = "SELECT * FROM emails WHERE requestId = '".$requestId."'";
		$rs1 = $db->Execute($sql);
		$recCount = $rs1->RecordCount();

		if($recCount==0 || $recCount==''){
                    if(trim($message)!=''){
			$sql = "INSERT INTO emails SET
                                name = '".$name."',
                                email = '".$email."',
                                phone = '".$phone."',
                                message = '".$message."',
                                email_date = '".$createdAt_db."',
                                requestId = '".$requestId."',
                                client_id = '".$rs->fields['unbounce_id']."'";
			$res = $db->Execute($sql);
                    }
		}
	}

	// If you validate the user you may set the user cookies/sessions here
		#setcookie("logged_in", "user_id");
		#$_SESSION["logged_user"] = "user_id";
	
	// Set the redirect url after successful login
	//if($rs->fields['account_type']=='admin'){
		//$resp['redirect_url'] = 'admin.php?act=dashboard';
	//}else{
		$resp['redirect_url'] = 'admin.php?act=dashboard';
	//}
}
echo json_encode($resp);