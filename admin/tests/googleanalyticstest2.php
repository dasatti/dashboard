<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


ini_set('display_errors', 1); 
error_reporting(E_ERROR);



require_once realpath(dirname(__FILE__).'/../classes/dashboard/GoogleAnalyticsServiceHandler.php');



$client_email = '337908885557-4scvq2itd2pd9io78k49nb2r6phg2bk1@developer.gserviceaccount.com';
$key_file = realpath(dirname(__FILE__).'/../data/keys.p12');

$ga_handler = new GoogleAnalyticsServiceHandler($client_email,$key_file);
$ga_handler->set_profile_id('ga:90000741');

$data = $ga_handler->get_analytics();

echo "Page Views -> ".$data->totalsForAllResults['ga:pageviews']; 
echo "<br>Unique visitors -> ".$data->totalsForAllResults['ga:visitors']; 
print_r($data);


echo '<hr>Dimensional Data<hr>';
$ga_handler->set_analytics_start_date('2015-01-01');
$ga_handler->set_analytics_end_date('2015-12-30');
$data2 = $ga_handler->get_monthly_analytics();
print_r($data2);





?>
