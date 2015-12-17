<?php
set_time_limit(0);
ini_set("memory_limit","500M");

if($_GET['mode'] == 'import' && $_GET['act'] == 'manageemails'){
	
	$unbounce_id = $_SESSION['lm_auth']['tbl_id'];
	$url = "https://api.unbounce.com/pages/".$unbounce_id."/leads";
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
	
        if($unbounce_id =='d65bbb90-8124-11e4-a2c1-22000a91bbb2'){
		//www.highqualitycoffeecapsules.com/
		$namefield = 'name';
		$emailfield = 'emailAddress';
		$phonefield = 'contactNumber';
		$messagefield = 'howWeMayAssistYou';
		
	}elseif($unbounce_id =='e85e7926-9642-11e4-b1fb-22000b252516'){
		//www.beverlyhillsuae.com/
		$namefield = 'fullName';
		$emailfield = 'email';
		$phonefield = 'phoneNumber';
		$messagefield = 'message';
		
	}elseif($unbounce_id =='3f03cf50-8a8c-11e4-8081-22000b380175'){
		//www.braces-dubai.com/
		$namefield = 'name';
		$emailfield = 'email';
		$phonefield = 'phoneNo';
		$messagefield = 'message';
		
	}elseif($unbounce_id =='638e30b4-53a9-11e4-b12f-22000b300054'){
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
		$createdAt  = $arr_single['createdAt'];
		$createdAt_arr = explode("T",$createdAt);
		
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
									email_date = '".$createdAt_arr[0]."',
									requestId = '".$requestId."',
									client_id = '".$unbounce_id."'";
			$res = $db->Execute($sql);
                    }
		}
	}
	header("Location:admin.php?act=manageemails");
	exit;
}
	   
?>