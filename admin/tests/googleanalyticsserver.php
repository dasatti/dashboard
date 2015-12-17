<?php
ini_set('display_errors', 1); 
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__).'/../libs/google-api-php-client/autoload.php');
//
$client_email = '337908885557-4scvq2itd2pd9io78k49nb2r6phg2bk1@developer.gserviceaccount.com';
//$client_email = '337908885557-4scvq2itd2pd9io78k49nb2r6phg2bk1.apps.googleusercontent.com';
$private_key = file_get_contents('../data/keys.p12');

$scopes = array('https://www.googleapis.com/auth/analytics.readonly');
$credentials = new Google_Auth_AssertionCredentials(
    $client_email,
    $scopes,
    $private_key
);

$client = new Google_Client();
//$client->setAuthConfigFile('sec.json');
$client->setAssertionCredentials($credentials);
if ($client->getAuth()->isAccessTokenExpired()) {
  $client->getAuth()->refreshTokenWithAssertion();
}

$service = new Google_Service_Analytics($client);    


$ids = "ga:90000741";//ga:96112309,ga:90000741
$start_date = "2010-01-01";
$end_date = "2015-12-30";
//$metrics = "ga:visits,ga:pageviews";
$metrics="ga:pageviews,ga:visitors";
//$dimensions = "ga:browser";
$dimensions = "ga:country,ga:region,ga:city,ga:hour";
$dimensions = "";
$optParams = array('dimensions' => $dimensions);
$data = $service->data_ga->get($ids,$start_date,$end_date,$metrics,$optParams);
   
echo '<pre>';
 print_r($data);

 
echo "Page Views -> ".$data->totalsForAllResults['ga:pageviews']; 
echo "<br>Unique visitors -> ".$data->totalsForAllResults['ga:visitors']; 
