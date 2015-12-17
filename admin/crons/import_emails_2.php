<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
set_time_limit(0);
ini_set("memory_limit","500M");
error_reporting(E_ALL);

include('../../adodb/adodb.inc.php');
include('../../include/siteconfig.inc.php');
include('../../include/sitefunction.php');
$db = ADONewConnection('mysql');
$db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");
	

//fetch all clients with valid unbounce ids

$s1 = "SELECT unbounce_id FROM tbl_admin WHERE unbounce_id LIKE '%-%-%-%-%'";
$r1 = $db->Execute($s1);

$unbounce_meta = array(
    'd65bbb90-8124-11e4-a2c1-22000a91bbb2' => array(
        "website"=>"www.highqualitycoffeecapsules.com/",
        "nameField"=>"name",
        "emailfield" => 'emailAddress',
        "phonefield" => 'contactNumber',
        "messagefield" => 'howWeMayAssistYou'
        ),
    'e85e7926-9642-11e4-b1fb-22000b252516'=> array(
        "website"=>"www.beverlyhillsuae.com/",
        "nameField"=>"fullName",
        "emailfield" => 'email',
        "phonefield" => 'phoneNumber',
        "messagefield" => 'message'),
    '3f03cf50-8a8c-11e4-8081-22000b380175'=> array(
        "website"=>"www.braces-dubai.com/",
        "nameField"=>"name",
        "emailfield" => 'email',
        "phonefield" => 'phoneNo',
        "messagefield" => 'message'),
    '638e30b4-53a9-11e4-b12f-22000b300054'=> array(
        "website"=>"www.rentacar-dubai.com/",
        "nameField"=>"yourName",
        "emailfield" => 'yourEmail',
        "phonefield" => 'phoneNumber',
        "messagefield" => 'message'),
    'default'=> array(
        "website"=>"",
        "nameField"=>"name",
        "emailfield" => 'email',
        "phonefield" => 'phoneNumber',
        "messagefield" => 'message')
);




while(!$r1->EOF){
    $unbounce_id = $r1->fields['unbounce_id'];
    $url = "https://api.unbounce.com/pages/".$unbounce_id."/leads";
    $url_form_fields = "https://api.unbounce.com/pages/".$unbounce_id."/form_fields";
    $username = '5e319884847c030a1e83707ba7af5126';
    $password = '';
    
    $ff_req = curl_init();
    curl_setopt($ff_req, CURLOPT_URL, $url_form_fields); 
    curl_setopt($ff_req, CURLOPT_HEADER, 1);   
    curl_setopt($ff_req, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', 'Content-Type: application/xml'));        
    curl_setopt($ff_req, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($ff_req, CURLOPT_TIMEOUT, 30);      
    curl_setopt($ff_req, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ff_req, CURLOPT_SSL_VERIFYPEER, false);
    $ff_res = curl_exec($ff_req);  
    curl_close($ff_req);
    $ff_res_json = explode("Connection: keep-alive",$ff_res);
    echo '<hr><hr>Form Fields<hr><pre>';
    print_r($ff_res_json);
    $ff_data = json_decode($ff_res_json[1], true);
    foreach($ff_data['form_fields'] as $ff){
        //echo $ff;echo "<br>";
    }
    
    
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
    $data = json_decode($arr_json[1], true);
    echo '</pre><hr><hr>Leads<hr><pre>';
    echo var_dump($data);
    print_r($arr_json);echo '</pre><hr>Leads End<hr>';
    
    foreach($data['leads'] as $rec){
        
        if(array_key_exists($unbounce_id, $unbounce_meta)){
            $namefield = $unbounce_meta[$unbounce_id]['nameField'];
            $emailfield = $unbounce_meta[$unbounce_id]['emailfield'];
            $phonefield = $unbounce_meta[$unbounce_id]['phonefield'];
            $messagefield = $unbounce_meta[$unbounce_id]['messagefield'];
        } else {
            $namefield = $unbounce_meta['default']['nameField'];
            $emailfield = $unbounce_meta['default']['emailfield'];
            $phonefield = $unbounce_meta['default']['phonefield'];
            $messagefield = $unbounce_meta['default']['messagefield'];
        }
        
        $name	  = $rec['formData'][$namefield][0];
        $email	  = $rec['formData'][$emailfield][0];
        $phone	  = $rec['formData'][$phonefield][0];
        $message  = $rec['formData'][$messagefield][0];
        
        $requestId  = $rec['extraData']['requestId'];
        $createdAt  = $rec['createdAt'];
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
    
   echo 'Cron: email import completed...<br>';
   
    
    $r1->MoveNext();
}


   $con=date("Y-m-d H:i:s");
   file_put_contents("cron.log", $con);


?>
