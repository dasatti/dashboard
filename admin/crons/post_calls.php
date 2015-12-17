<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('UB_USER', '5e319884847c030a1e83707ba7af5126');
define('UB_PASS', "");
define('UB_TEST_CLIENT_ID', 'e85e7926-9642-11e4-b1fb-22000b252516');



$url = "https://api.unbounce.com/pages/".UB_TEST_CLIENT_ID."/leads";

$form_string.="form_submission[form_data][full_name]=DanishSatti";
$form_string.="&form_submission[form_data][email]=test2@test.com&form_submission[form_data][gender]=male";
$form_string.="&form_submission[submitter_ip]=127.0.0.1&form_submission[variant_id]=a";

//$url.="/?".$form_string;

//echo $form_string;die;

$process = curl_init();
curl_setopt($process, CURLOPT_URL, $url); 
curl_setopt($process, CURLOPT_HEADER, 1); 
curl_setopt($process, CURLOPT_USERPWD, UB_USER . ":" . UB_PASS);
curl_setopt($process, CURLOPT_TIMEOUT, 30);      
curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE); 
//curl_setopt($process, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($process, CURLOPT_POST, 1);
curl_setopt($process, CURLOPT_POSTFIELDS,$form_string);

$response = curl_exec($process);  
curl_close($process);

echo $response;


?>


<form method="post" action="<?php echo $url; ?>">
    
    <label>Full name :</label><input type="text" name="form_submission[form_data][firstname]"> <br>
    <label>Email :</label><input type="text" name="form_submission[form_data][email]"> <br>
    <label>IP :</label><input type="text" name="form_submission[submitter_ip]" value="127.0.0.1"> <br>
    <label>Variant :</label><input type="text" name="form_submission[variant_id]" value="a"> <br>
    <input type="submit">
    
</form>