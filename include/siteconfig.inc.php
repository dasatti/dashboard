<?php session_start();
#######################
#
# Data Base Connection
#
#######################
error_reporting(1);
//define('DBHOST', 'localhost'); 
//define('DBUSER', 'lmagency');
//define('DBPASS', 'Local@2014');
//define('DBNAME', 'lmgsm');
//
//define('SURL', 'http://'.$_SERVER['HTTP_HOST'].'/agency-dashboard/admin/');
//define('MYSURL', 'http://'.$_SERVER['HTTP_HOST'].'/agency-dashboard/');

define('ROOT',"../");

define('DBHOST', 'localhost'); 
define('DBUSER', 'root');
define('DBPASS', 'dasatti');
define('DBNAME', 'dashboard');

//define('DBHOST', '23.229.139.96'); 
//define('DBUSER', 'lmagency');
//define('DBPASS', 'Local@2014');
//define('DBNAME', 'lmgsm');

ini_set("display_error", 1);
error_reporting(E_ALL);

$sub = "/dashboard";
if(strstr($_SERVER['HTTP_HOST'],'dev1')){
	$sub = '/agency-dashboard';	
}
define('SURL', 'http://'.$_SERVER['HTTP_HOST'].$sub.'/admin/');
define('MYSURL', 'http://'.$_SERVER['HTTP_HOST'].$sub.'/');


define('TITLE', 'Test Site');
define('ADMIN_TITLE',"LM DIGITAL AGENCY 2.0");

$tblprefix= 'tbl_';
define('SECURITY_CHECK',"1");

$server_arr = explode("/",$_SERVER['REQUEST_URI']);
$page_name = $server_arr[count($server_arr)-1];
include(dirname(__FILE__).'/../adodb/adodb.inc.php');
$db = ADONewConnection('mysqli');
$db->debug = true;
$db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");

?>