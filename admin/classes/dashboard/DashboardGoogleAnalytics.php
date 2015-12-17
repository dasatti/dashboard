<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DashboardGoogleAnalytics
 *
 * @author danish
 */


require_once dirname(__FILE__).'/DashboardCommon.php';


class DashboardGoogleAnalytics extends DashboardCommon{
    //put your code here
    
    private $ga_client_email;
    
    private $ga_key_file;
    
    private $analytics_data;

    private $page_views;
    
    private $unique_visitors;
    
    private $chart_analytics;
    
    private $to;
    
    private $from;
    
    private $analytics = false;
    
    private $period;
    
    private $ga_handler;
    
    public function __construct() {
        parent::__construct();
        
        $this->ga_client_email = '337908885557-4scvq2itd2pd9io78k49nb2r6phg2bk1@developer.gserviceaccount.com';
        
        $this->ga_key_file = realpath(dirname(__FILE__).'/../../data/keys.p12');
        
        $this->setPeriod('lifetime');
    }
    
    
    public function setPeriod($period){
        $this->period = $period;
        
        $rangeArray = getDateRangeFromPeriod($this->period);
        $this->from = $rangeArray['date_from'];
        $this->to = $rangeArray['date_to'];
    }
    
    public function setCustomPeriod($from,$to){
        $this->period = 'custom';
        $from = strtotime($from);
        $to = strtotime($to);
        $this->from = date("Y-m-d", $from);
        $this->to = date("Y-m-d", $to);
    }


    public function update_analytics(){
        require_once realpath(dirname(__FILE__). '/GoogleAnalyticsServiceHandler.php');
        
        $this->ga_handler = new GoogleAnalyticsServiceHandler($this->ga_client_email,  $this->ga_key_file);
        if($this->get_ga_view_id()==''){
            //if google analatics view id is not present return empty data
            $this->analytics_data = array();
            $this->chart_analytics = array();
            $this->page_views = 0;
            $this->unique_visitors = 0;
            $this->analytics = true;
            return;
        }
        $profile_id = 'ga:'.$this->get_ga_view_id();
        $this->ga_handler->set_profile_id($profile_id);
        $this->ga_handler->set_analytics_start_date($this->from);
        $this->ga_handler->set_analytics_end_date($this->to);
        $this->analytics_data = $this->ga_handler->get_analytics();
        //$this->ga_handler->set_metrics('ga:pageviews,ga:visitors');
        //$this->chart_analytics = $this->ga_handler->get_monthly_analytics();
        $this->chart_analytics = $this->get_chart_analytics_from_period();
        //echo $this->analytics_data->totalsForAllResults['ga:pageviews'];
        $this->page_views = $this->analytics_data->totalsForAllResults['ga:pageviews'];
        $this->unique_visitors = $this->analytics_data->totalsForAllResults['ga:visitors'];
        $this->analytics = true;
    }
    
    public function get_analytics_data(){
        if(!$this->analytics) $this->update_analytics ();
        return $this->analytics_data;
    }
    
    
    public function get_page_views(){
        if(!$this->analytics) $this->update_analytics ();
        return $this->page_views;
    }
    
    public function get_unique_visitors(){
        if(!$this->analytics) $this->update_analytics ();
        return $this->unique_visitors;
    }
    
    public function get_chart_data(){
        if(!$this->analytics) $this->update_analytics ();
        return $this->chart_analytics;
    }
    
    public function get_chart_analytics_from_period(){
        
        $this->ga_handler->set_metrics('ga:pageviews,ga:visitors');
        switch ($this->period){
            case 'lifetime':
                $chart_data = $this->ga_handler->get_monthly_analytics();
                break;
            case 'month':
            case 'week':
            case 'last_30_days':
            case 'this_month':
            case 'last_month':
            case 'last_7_days':
                $chart_data = $this->ga_handler->get_daily_analytics();
                break;
            case 'daily':
            case 'today':
            case 'yesterday':
                $chart_data = $this->ga_handler->get_hourly_analytics();
                break;
            case 'custom':
            case 'default':
                $chart_data = $this->ga_handler->get_monthly_analytics();
                break; 
        }
        return $chart_data;
    }
    
}

?>
