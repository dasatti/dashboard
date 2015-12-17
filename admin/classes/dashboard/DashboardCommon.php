<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DashboardCommon
 *
 * @author danish
 */


require_once dirname(__FILE__).'/../../../adodb/adodb.inc.php';


class DashboardCommon {
    //put your code here
    
    private static  $db;
    
    private static $conn = false;
    
    private static $is_su = false;
    
    private static $client_id;//id column from tbl_admin
    
    private $client_gsm_id;
    
    
    private $client_unbounce_ids = array();
    
    private $ga_view_id;
    


    public function __construct() {
        DashboardCommon::$is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
        
        $gsm_temp = $_SESSION['lm_auth']['client_id'];
        $gsm_id = "";
        if(!empty($gsm_temp)){
                $gsm_id_arr = explode("#",$gsm_temp);

            foreach($gsm_id_arr as $gsm){
                    $gsm_ids.=$gsm.',';	
            }
            $gsm_id = rtrim($gsm_ids,',');
        }
        $this->client_gsm_id = $gsm_id;
        
        
        
        $this->ga_view_id = $_SESSION['lm_auth']['ga_view_id'];
        
        
        
        
        $this->client_unbounce_ids = $this->fetch_unbounce_ids();
        
    }
    
    private static function db_connect(){
        DashboardCommon::$db = ADONewConnection('mysqli');
        DashboardCommon::$db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");
        DashboardCommon::$conn = true;
    }
    
    public static function db(){
        if(!DashboardCommon::$conn) DashboardCommon::db_connect ();
        return DashboardCommon::$db;
    }
    
    public static function is_su(){
        return DashboardCommon::$is_su;
    }
    
    public static function get_client_id(){
        DashboardCommon::$client_id = $_SESSION['lm_auth']['id'];
        return DashboardCommon::$client_id;
    }

    public function get_gsm_number(){
        return $this->client_gsm_id;
    }
    
    
    public function get_ga_view_id(){
        return $this->ga_view_id;
    }

    public function fetch_unbounce_ids(){
        $sql = "SELECT unbounce_id FROM campaigns WHERE client_id=".DashboardCommon::get_client_id();
        $r = DashboardCommon::db()->Execute($sql);
        $unbounce_ids = array();
        
        if($r){
            while(!$r->EOF){
                $unbounce_ids[] = $r->fields['unbounce_id'];
                $r->MoveNext();
            }
        }
        
        return $unbounce_ids;
    }
    
    public function get_unbounce_ids(){
        if(empty($this->client_unbounce_ids)) return "";
        return $this->client_unbounce_ids;
    }
    
    
    public static function get_clients(){
        $sql = "SELECT * FROM tbl_admin WHERE account_type='client'";
        $r = DashboardCommon::db()->Execute($sql);
        $clients = array();
        if($r){
            foreach($r as $res){
                $clients[] = $res;
            }
        }
        
        return $clients;
    }
    
    public static function getFirstCampaignStartDate(){
        $client = "";
        if(!DashboardCommon::is_su()){
            $client = "client_id='".  DashboardCommon::get_client_id()."' AND";
        }
        $sql = "SELECT MIN(start_date) as start_date FROM campaigns WHERE $client start_date!='0000-00-00'";
        $r = DashboardCommon::db()->Execute($sql);
        return $r->fields['start_date'];
    }

    public static function getDateRangeFromPeriod($period){
        $dateRange = array();

        switch ($period){
            case 'lifetime':
                $from = '2014-09-01';
                $from = DashboardCommon::getFirstCampaignStartDate();
                $to = date('Y-m-d',strtotime("+1 days"));
                break;
            case 'month':
                $from = date('Y-m-d',strtotime("-30 days"));$to = date('Y-m-d',strtotime("+1 days"));
                break;
            case 'week':
                $from = date('Y-m-d',strtotime("-6 days"));$to = date('Y-m-d',strtotime("+1 days"));
                break;
            case 'daily':
            case 'today':
                $from = date('Y-m-d');$to = date('Y-m-d',strtotime("+1 days"));
                break;
            case 'yesterday':
                $from = date('Y-m-d',strtotime("-1 days"));$to = date('Y-m-d');
                break;
            case 'last_7_days':
                $from = $calls_date_from = date('Y-m-d',strtotime("-6 days"));
                $to = date('Y-m-d',strtotime("+1 days"));
                break;
            case 'last_30_days':
                $from = date('Y-m-d',strtotime("-30 days"));$to = date('Y-m-d',strtotime("+1 days"));
                break;
            case 'this_month':
                $from = date('Y-m-01');$to = date('Y-m-d',strtotime("+1 days"));
                break;
            case 'last_month':
                $from = date("Y-m-d", strtotime("first day of previous month"));
                $to  = date("Y-m-d", strtotime("last day of previous month"));
                break;
            case 'custom':
                $timestamp_from = strtotime(trim($_REQUEST['from']));
                $from = date("Y-m-d", $timestamp_from);
                $timestamp_to = strtotime(trim($_REQUEST['to']));
                $to = date("Y-m-d", $timestamp_to);
                break;
        }
        $dateRange['date_from'] = $from;
        $dateRange['date_to'] = $to;
        return $dateRange;
    }
    
    
    function timeInAmPm($dateTime){
         $final_time = date("H:i A", strtotime($dateTime));
             return $final_time;
    }

    function timeInAmPmShort($dateTime){
         $final_time = date("H A", strtotime($dateTime));
             return $final_time;
    }

    public static function createDateRangeArray($strDateFrom,$strDateTo)
    {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.

        // could test validity of dates here but I'm already doing
        // that in the main script

        $aryRange=array();

        $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
        $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

        if ($iDateTo>=$iDateFrom)
        {
            array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
            while ($iDateFrom<$iDateTo)
            {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange,date('Y-m-d',$iDateFrom));
            }
        }
        return $aryRange;
    }
    
    
    public static function initDateRangeFilter($period){
        $_SESSION['lm_conf']['period'] = $period;
        
        $date_range = DashboardCommon::getDateRangeFromPeriod($period);
        $_SESSION['lm_conf']['period_from'] = $date_range['date_from'];
        $_SESSION['lm_conf']['period_to'] = $date_range['date_to'];
    }
    
    public static function saveDateRangeFilter($period,$to,$from){
        $_SESSION['lm_conf']['period'] = $period;
        $_SESSION['lm_conf']['period_from'] = $from;
        $_SESSION['lm_conf']['period_to'] = $to;
    }
    
    public static function saveDateRangeFilterCustom($to,$from){
        
        $_SESSION['lm_conf']['period'] = 'custom';
        $_SESSION['lm_conf']['period_from'] = $from;
        $_SESSION['lm_conf']['period_to'] = $to;
    }
    
    public static function getSavedDateRangeFilter(){
        if(empty($_SESSION['lm_conf']['period'])){
            DashboardCommon::initDateRangeFilter('lifetime');
        }
        return array('period'=>$_SESSION['lm_conf']['period'],
            'from'=>$_SESSION['lm_conf']['period_from'],'to'=>$_SESSION['lm_conf']['period_to']);
    }
    
    public static function getDateRangePeriod(){
        if(empty($_SESSION['lm_conf']['period']))
            DashboardCommon::initDateRangeFilter('lifetime');
        return $_SESSION['lm_conf']['period'];
    }
    
    public static function getDateRangePeriodFrom(){
        if(empty($_SESSION['lm_conf']['period']))
            DashboardCommon::initDateRangeFilter('lifetime');
        return $_SESSION['lm_conf']['period_from'];
    }
    
    public static function getDateRangePeriodTo(){
        if(empty($_SESSION['lm_conf']['period']))
            DashboardCommon::initDateRangeFilter('lifetime');
        return $_SESSION['lm_conf']['period_to'];
    }

}

?>
