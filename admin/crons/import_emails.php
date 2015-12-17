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


$debug = false;
if(isset($_REQUEST['debug'])) $debug=$_GET['debug'];

$db = ADONewConnection('mysql');
$db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");
	

//fetch all clients with valid unbounce ids

$s1 = "SELECT campaigns.unbounce_id FROM campaigns WHERE unbounce_id LIKE '%-%-%-%-%'";
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
    'b8c458b8-add1-47dc-91b3-32f75600600e'=> array(
        "website"=>"http://www.business-setup.net/",
        "nameField"=>"name",
        "emailfield" => 'email',
        "phonefield" => 'phoneNo',
        "messagefield" => 'message'),
    'ae560214-9519-11e4-a0c3-22000b6984f8'=> array(
        "website"=>"http://www.nabatcoffee.com/",
        "nameField"=>"fullName", //bin nabat 02
        "emailfield" => 'email',
        "phonefield" => 'phoneNumber',
        "messagefield" => 'howWeMayAssistYou'),
    '804ef5fa-94e8-11e4-bf16-22000b380175'=> array(
        "website"=>"http://www.nabat-coffee.com/",
        "nameField"=>"fullName", //bin nabat 
        "emailfield" => 'emailAddress',
        "phonefield" => 'contactNo',
        "messagefield" => 'howWeMayAssistYou'),
    '189f12ce-4a46-454d-a110-80c67d801282'=> array(
        "website"=>"http://www.dubaimovers.me/",
        "nameField"=>"name", //We move
        "emailfield" => 'email',
        "phonefield" => 'phone_number',
        "messagefield" => 'message'),
    'default'=> array(
        "website"=>"",
        "nameField"=>"name",
        "emailfield" => 'email',
        "phonefield" => 'phoneNumber',
        "messagefield" => 'message')
);

$extra_form_data = array('country'=>'country','gender'=>'gender');//formData=>column_name

//$from = date('Y-m-d\TH:i:s.00\Z',strtotime("-60 days"));
$from = date(\DateTime::RFC3339,strtotime("-60 days"));

while(!$r1->EOF){
    //echo $r1->fields['unbounce_id'],"<br>";
    $unbounce_id = $r1->fields['unbounce_id'];
    echo $url = "https://api.unbounce.com/pages/".$unbounce_id."/leads?from=".$from."&limit=1000";
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
    $data = json_decode($arr_json[1], true);
//    $c=0;
    foreach($data['leads'] as $rec){
        if($debug) {echo '<hr>'; print_r($rec);echo '<hr>';}
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
        
        $extra_array = array();
        foreach($extra_form_data as $key=>$val){
            if(array_key_exists($key,$rec['formData'])){
                $extra_array[$key] = $rec['formData'][$val][0];
            }
        }
        $sql_1 = "";
        $sql_2 = "";
        if(!empty($extra_array)){
            //$sql_1 = ','.implode(',', $extra_array);
            foreach ($extra_array as $key=>$val){
                $sql_1 = ','.$key;
                $sql_2 = ',\''.$val.'\'';
            }
        }
        
        
        $requestId  = $rec['extraData']['requestId'];
        $createdAt  = $rec['createdAt'];
        $createdAt_arr = explode("T",$createdAt);
        
        $sql = "SELECT * FROM emails WHERE requestId = '".$requestId."'";
        $rs1 = $db->Execute($sql);
        $recCount = $rs1->RecordCount();

        if(!$recCount>0){
                
                $sql = "INSERT INTO emails(name,email,phone,message,email_date,requestId,client_id $sql_1)
                    VALUES('$name','$email','$phone','".mysql_escape_string($message)."','".$createdAt_arr[0]." ".$createdAt_arr[1]."',
                        '$requestId','$unbounce_id' $sql_2)";
                if($debug) {echo '<hr>inserting---><br>',$sql,'<hr>';}
                $res = $db->Execute($sql);
            
        }
    
    }
    
   if($debug) echo '<br>Cron: email import completed...<br>';
   
    
    $r1->MoveNext();
}


   $con=date("Y-m-d H:i:s");
   file_put_contents("cron.log", $con);
   echo "1";


?>
