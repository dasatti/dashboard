<?php

set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

// include('../../adodb/adodb.inc.php');
include('../../include/siteconfig.inc.php');
include('../../include/sitefunction.php');


// $db = ADONewConnection(DBENGINE);
// $db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");
  

define('CLIENT_ID','00000000-0000-0000-0001-000000000001');
define('RECORD_FILE','./luzan_dental_import');



$spreadsheet_url="https://docs.google.com/spreadsheets/d/1o1S0g54SmLJz05SjfUrGOcCw3YRNUjxw1rr2uiHfww8/pub?output=csv";


if(!ini_set('default_socket_timeout',    15)) echo "<!-- unable to change socket timeout -->";

if (($handle = fopen($spreadsheet_url, "r")) !== FALSE) {


  $last_record = 0;
  if(file_exists(RECORD_FILE)){
    $fh = fopen(RECORD_FILE, 'r');
    $last_record = (int)fread($fh,filesize(RECORD_FILE));
  }
  
  fclose($fh);


  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      // $spreadsheet_data[]=$data;
      if(!(int)$data[0]>=1 || ((int)$data[0]<=$last_record && $last_record>0)) continue;
      $spreadsheet_data[] = array('s_no'=>$data[0],
        'date'=>$data[1], 'name'=>$data[2], 'email'=>$data[3], 'phone'=>$data[4],
        'message'=>$data[5]);
      $sql = "INSERT INTO emails(email_date,name,email,phone,country,message,client_id)
            VALUES('".date('Y-m-d H:i:s',strtotime($data[1].' +12 hours'))."','".$data[2]."','".$data[3]."','".$data[4]."','','".$data[5]."','".CLIENT_ID."')";
      echo $sql,'<br>';
      $last_record = $data[0];
      $res = $db->Execute($sql) or die($db->ErrorMsg());
    }

  fclose($handle);
  $fh = fopen(RECORD_FILE, 'w');
  fwrite($fh,$last_record);
  fclose($fh);
}
else
    die("Problem reading csv");

// print_r(json_encode($spreadsheet_data));