<?php


/**
 * Server to Server based authentication and access to Google Analytics data
 * Requires: https://github.com/google/google-api-php-client
 * 
 * 
 * Minimal Example
 * $ga_handler = new GoogleAnalyticsServiceHandler($client_email,$key_file);
 * $ga_handler->set_profile_id('ga:XXXXXXXX');
 * print_r($ga_handler->get_analytics());
 * 
 * More detailed example
 * $ga_handler = new GoogleAnalyticsServiceHandler($client_email,$key_file);
 * $ga_handler->set_profile_id('ga:XXXXXXXX');
 * $ga_handler->set_analytics_start_date('2014-01-01');
 * $ga_handler->set_analytics_start_date('2015-12-31');
 * $ga_handler->set_dimensions("ga:country,ga:region,ga:city");
 * $data = $ga_handler->get_analytics();
 * echo "Page Views -> ".$data->totalsForAllResults['ga:pageviews']; 
 *
 * @author danish <dasatti@gmail.com>
 * Last update 22/04/2015
 */




// download Google Client PHP library from here: https://github.com/google/google-api-php-client

require_once realpath(dirname(__FILE__).'/../../libs/google-api-php-client/src/Google/autoload.php');


class GoogleAnalyticsServiceHandler {
    //put your code here
    
    /**
     *
     * @var type Google_Client
     */
    private $client;
    
    /**
     *
     * @var type Google_Auth_AssertionCredentials
     */
    private $credentials;
    
    /**
     *
     * @var type Google_Service_Analytics
     */
    private $analytics_service;
    
    /**
     *
     * @var type string required
     */
    private $client_email = '337908885557-4scvq2itd2pd9io78k49nb2r6phg2bk1@developer.gserviceaccount.com';
    
    /**
     * path to private key .p12 file
     * @var type string required
     */
    private $private_key;
    
    /**
     * contains goggle analytics scope strings
     * eg. https://www.googleapis.com/auth/analytics.readonly
     * 
     * @var type array
     */
    private $scopes = array();
    
    /**
     *
     * @var type boolean
     */
    private $is_authentivated = false;
    
    /**
     *
     * @var type boolean
     */
    private $is_service_initiated = false;
    
    /**
     * data array returned by google analytics
     * 
     * @var type array
     */
    private $data;
    
    /**
     * profile id of google analytics account
     * 
     * @var type string requried format: "ga:XXXXXXXX"
     */
    private $profile_id;
    
    /**
     * 
     * @var type string format: "ga:visits,ga:pageviews,ga:bounces" etc
     * @info https://developers.google.com/analytics/devguides/reporting/core/dimsmets
     */
    public $metrics ;
    
    /**
     *
     * @var type string e.g 'ga:date,ga:year,ga:month,ga:day'
     * @info https://developers.google.com/analytics/devguides/reporting/core/dimsmets
     */
    public $dimension = "";
    
    /**
     *
     * @var type string e.g 'ga:year,ga:month'
     */
    public $sort;
    
    /**
     * start date to fetch analytics data
     * default: 30 days back
     * 
     * @var type Date format YYYY-MM-DD
     */
    public $start_date;
    
    /**
     * End date to fetch analytics data
     * default: current date
     * 
     * @var type Date format YYY-MM-DD
     */
    public $end_date;



    /**
     * 
     * @param type $client_email google analytics api email
     * @param type $private_key_file 
     */
    public function __construct($client_email,$private_key_file) {
        
        try{
            
            $this->set_client_email($client_email);
            $this->set_private_key($private_key_file);
            
            $this->add_analytics_readonly_scope();
            $this->set_analytics_end_date(date('Y-m-d'));
            //default to last 30 days
            $this->set_analytics_start_date(date('Y-m-d',strtotime("-30 days")));
            $this->set_metrics("ga:pageviews,ga:visitors,ga:visits,ga:uniquePageviews");
            $this->dimension = "";
            
        } catch(Exception $e){
            echo $e->getMessage();die;
        }
    }
    
    /**
     * set google analytics api client email
     * 
     * @param type $client_email
     */
    private function set_client_email($client_email){
        //e.g 'XXXXXXXXXXXX-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX@developer.gserviceaccount.com'
        $this->client_email = $client_email;
    }
    
    /**
     * set google analytics API generater .p12 key file path
     * 
     * @param type $key_path
     * @throws Exception
     */
    private function set_private_key($key_path){
        if(!file_exists($key_path))
            throw new Exception("Private key file not found");
        $this->private_key = file_get_contents($key_path);
    }

    /**
     * set scopes for querying google API
     * 
     * @param type $scope
     */
    public function add_scope($scope){
        //e.g. https://www.googleapis.com/auth/analytics.readonly
        array_push($this->scopes, $scope);
    }
    
    /**
     * set default scope for this class
     */
    public function add_analytics_readonly_scope(){
        $this->add_scope('https://www.googleapis.com/auth/analytics.readonly');
        
    }
    
    /**
     * unset all scopes
     */
    public function clear_scopes(){
        unset($this->scopes);
    }
    
    /**
     * set analaytics start date in YYYY-mm-dd format
     * 
     * @param type $date
     */
    public function set_analytics_start_date($date){
        $this->start_date = $date;
    }
    
    /**
     * set analaytics end date in YYYY-mm-dd format
     * 
     * @param type $date
     */
    public function set_analytics_end_date($date){
        $this->end_date = $date;
    }
    
    /**
     * set google analytics API profile id, format : ga:XXXXXXXX
     * 
     * @param type $profile_id
     */
    public function set_profile_id($profile_id){
        $this->profile_id = $profile_id;
    }
    
    /**
     * set google analytics API query metrics, eg. "ga:pageviews,ga:visitors"
     * 
     * @param type $metrics
     */
    public function set_metrics($metrics){
        $this->metrics = $metrics;
    }
    
    /**
     * set google analytics API dimensions, eg. "ga:country,ga:region,ga:city,ga:hour"
     * @param type $dims
     */
    public function set_dimensions($dims){
        $this->dimension = $dims;
    }
    
    /**
     * 
     * @param type $sort dimesions to apply sort e.h "ga:year,ga:month"
     */
    public function set_sort($sort){
        $this->sort = $sort;
    }

    /**
     * authentcate google client
     * 
     * @throws Exception
     */
    public function authenticate(){
        
        if($this->client_email=='') throw new Exception('Client email not set');
        
        if(empty($this->scopes)) throw new Exception('Scope not set');
        
        if($this->private_key=='') throw new Exception('Private key not supplied');
        
        $this->credentials = new Google_Auth_AssertionCredentials(
            $this->client_email,
            $this->scopes,
            $this->private_key
        );

        $this->client = new Google_Client();
        //$client->setAuthConfigFile('sec.json');
        $this->client->setAssertionCredentials($this->credentials);
        if ($this->client->getAuth()->isAccessTokenExpired()) {
          $this->client->getAuth()->refreshTokenWithAssertion();
        }
        
        $this->is_authentivated = true;
    }
    
    /**
     * initialize analytics API service
     */
    public function init_analytics_service(){
        
        if(!$this->is_authentivated)            
            $this->authenticate ();

        $this->analytics_service = new Google_Service_Analytics($this->client);   

        $this->is_service_initiated = true;
        
    }

    /**
     * execute all requirements and fetch data
     * 
     * @return type google analytics data
     * @throws Exception
     */
    public function get_analytics(){
        try{
            if(!$this->is_service_initiated){
                $this->init_analytics_service();
            }

            if($this->profile_id=='') throw new Exception('Analytics profile id not set');

            $parms = array();
            if($this->dimension!=''){
                $parms['dimensions'] = $this->dimension;
            }
            
            if($this->sort!=''){
                $parms['sort'] = $this->sort;
            }
                
            //echo $this->profile_id,':',$this->start_date,':',  $this->end_date,':',  $this->metrics;
            $this->data = $this->analytics_service->data_ga->get($this->profile_id,  $this->start_date,
                    $this->end_date,  $this->metrics, $parms);
            return $this->data;
        } catch (Exception $e){
            echo $e->getMessage();die;
        }
    }
    
    /**
     * 
     * @param type $dims e.g "ga:year,ga:month,ga:day"
     * @return type
     */
    public function get_dimensional_analytics($dims){
        $this->set_dimensions($dims);
        return $this->get_analytics();
    }
    
    
    /**
     * 
     * @return type
     */
    public function get_hourly_analytics(){
        $this->set_dimensions("ga:day,ga:hour");
        return $this->get_analytics();
    }
    
    /**
     * 
     * @return type
     */
    public function get_daily_analytics(){
        $this->set_dimensions("ga:day,ga:month");
        $this->set_sort('ga:month,ga:day');
        return $this->get_analytics();
    }
    
    /**
     * 
     * @return type
     */
    public function get_date_analytics(){
        $this->set_dimensions("ga:date,ga:month");
        $this->set_sort('ga:month,ga:date');
        return $this->get_analytics();
    }
    
    /**
     * 
     * @return type
     */
    public function get_weekly_analytics(){
        $this->set_dimensions("ga:week,ga:month");
        $this->set_sort('ga:month,ga:week');
        return $this->get_analytics();
    }
    
    /**
     * 
     * @return type
     */
    public function get_monthly_analytics(){
        $this->set_dimensions("ga:month,ga:year");
        $this->set_sort('ga:year,ga:month');
        return $this->get_analytics();
    }
    
    /**
     * 
     * @return type
     */
    public function get_yearly_analytics(){
        $this->set_dimensions("ga:year");
        return $this->get_analytics();
    }
    
    
    /**
     * 
     * @return type
     */
    public function get_continent_analytics(){
        $this->set_dimensions("ga:continent");
        return $this->get_analytics();
    }
    
    
    /**
     * 
     * @return type
     */
    public function get_country_analytics(){
        $this->set_dimensions("ga:country");
        return $this->get_analytics();
    }
    
    
    /**
     * 
     * @return type
     */
    public function get_region_analytics(){
        $this->set_dimensions("ga:region");
        return $this->get_analytics();
    }
    
    
    /**
     * 
     * @return type
     */
    public function get_city_analytics(){
        $this->set_dimensions("ga:city");
        return $this->get_analytics();
    }
    
    
    /**
     * 
     * @return type
     */
    public function get_country_iso_analytics(){
        $this->set_dimensions("ga:countryIsoCode");
        return $this->get_analytics();
    }
}

?>
