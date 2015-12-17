<?php       
ini_set('display_errors', 1); 
error_reporting(E_ALL);


require_once dirname(__FILE__).'/../libs/google-api-php-client/src/Google/autoload.php';
  
session_start();



$client = new Google_Client();
    $client->setApplicationName("Client_Library_Examples");
    $client->setDeveloperKey("AIzaSyB9kO2N3LlPdHuZ973svYIcD-D29aKWYfM");  
    $client->setClientId('337908885557-68e3afa2lnds0p102sq184u25an3lfi9.apps.googleusercontent.com');
    $client->setClientSecret('ZA7KeZnO8gN0on9Ibdt-5KBH');
    $client->setRedirectUri('http://localhost/dashboard/admin/libs/googleanalyticstest.php');
    $client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));

    //For loging out.
    if ($_GET['logout'] == "1") {
	unset($_SESSION['token']);
       }   

    // Step 2: The user accepted your access now you need to exchange it.
    if (isset($_GET['code'])) {
        
    	$client->authenticate($_GET['code']);  
    	$_SESSION['token'] = $client->getAccessToken();
    	$redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    	header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    }

    // Step 1:  The user has not authenticated we give them a link to login    
    if (!$client->getAccessToken() && !isset($_SESSION['token'])) {
    	$authUrl = $client->createAuthUrl();
    	print "<a class='login' href='$authUrl'>Connect Me!</a>";
        }        

    // Step 3: We have access we can now create our service
    if (isset($_SESSION['token'])) {
        print "<a class='logout' href='".$_SERVER['PHP_SELF']."?logout=1'>LogOut</a><br>";
    	$client->setAccessToken($_SESSION['token']);
    	$service = new Google_Service_Analytics($client);    

        // request user accounts
        $accounts = $service->management_accountSummaries->listManagementAccountSummaries();

       foreach ($accounts->getItems() as $item) {
		echo "Account: ",$item['name'], "  " , $item['id'], "<br /> \n";		
		foreach($item->getWebProperties() as $wp) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WebProperty: ' ,$wp['name'], "  " , $wp['id'], "<br /> \n";    
			
			$views = $wp->getProfiles();
			if (!is_null($views)) {
				foreach($wp->getProfiles() as $view) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;View: ' ,$view['name'], "  " , $view['id'], "<br /> \n";    
				}
			}
		}
	} // closes account summaries
        
        $ids = "ga:90000741";//ga:96112309|,ga:90000741
        $start_date = "2015-11-01";
        $end_date = "2015-11-30";
        $metrics = "ga:visits,ga:pageviews";
        $dimensions = "ga:browser";
        $optParams = array('dimensions' => $dimensions);
        $data = $service->data_ga->get($ids,$start_date,$end_date,$metrics,$optParams);
        
        print_r($data);

    }
 print "<br><br><br>";
 print "Access from google: " . $_SESSION['token']; 
?>