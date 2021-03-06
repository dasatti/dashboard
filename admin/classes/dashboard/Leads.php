<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Leads
 *
 * @author danish
 */

require_once dirname(__FILE__).'/DashboardCommon.php';


class Leads extends DashboardCommon{
    //put your code here
       
    private $period;
    
    private $to;
    
    private $from;
    
    private $calls_data_limit_clause = "";
    
    public function __construct() {
        parent::__construct();
        
        $start_date_limit = $_SESSION['lm_auth']['campaign_start'];
        $end_date_limit = $_SESSION['lm_auth']['campaign_end'];
        if($start_date_limit!="" && $start_date_limit!="0000-00-00" && !DashboardCommon::is_su()){
            $this->calls_data_limit_clause = " AND call_start>='$start_date_limit'";
        }
        if($end_date_limit!="" && $end_date_limit!="0000-00-00" && !DashboardCommon::is_su()){
            $this->calls_data_limit_clause = " AND call_end<='$end_date_limit'";
        }
        
        $this->setPeriod('lifetime');
    }
    
    public function setPeriod($period){
        $this->period = $period;
        
        $rangeArray = DashboardCommon::getDateRangeFromPeriod($this->period);
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
    

    public function getLeadsData(){
        if(!DashboardCommon::is_su()){
            //no super admin
            return array();
        }
        //only super admin below
        $tot_call = $tot_email = $tot_leads = 0;
        $sql1 = "SELECT count(*) as tot_calls FROM calls WHERE call_start >= '$this->from' AND call_start<='$this->to'
                ORDER BY call_start DESC";
        $r1 = DashboardCommon::db()->Execute($sql1);
        $sql2 = "SELECT count(*) as tot_email FROM emails WHERE email_date >= '$this->from' AND email_date<='$this->to'
                ORDER BY email_date DESC";
        $r2 = DashboardCommon::db()->Execute($sql2);

        $sql_leads = "SELECT * ,tcalls+temails as total_leads
            FROM
            (SELECT 
            COUNT(DISTINCT  DATE_FORMAT(call_start,'%y-%m-%d') , gsm_number) as tcalls
            FROM calls 
            WHERE (call_start>='$this->from' AND call_start<='$this->to')
            ) as a , 
            (SELECT 
            COUNT(*) as temails
            FROM emails 
            WHERE (email_date>='$this->from' AND email_date<='$this->to')
            ) as b";
        $r_sql_leads = DashboardCommon::db()->Execute($sql_leads);
        $tot_leads = $r_sql_leads->fields['total_leads'];

        $sql_calls_lifetime = "SELECT count(*) as tot_calls_lifetime FROM calls";
        $r3 = DashboardCommon::db()->Execute($sql_calls_lifetime);
        $sql_emails_lifetime = "SELECT count(*) as tot_emails_lifetime FROM emails";
        $r4 = DashboardCommon::db()->Execute($sql_emails_lifetime);
        $tot_call = ($r1->fields['tot_calls']!='')?$r1->fields['tot_calls']:0;
        $tot_email = ($r2->fields['tot_email']!='')?$r2->fields['tot_email']:0;
        $tot_calls_lifetime = ($r3->fields['tot_calls_lifetime']!='')?$r3->fields['tot_calls_lifetime']:0;
        $tot_emails_lifetime = ($r4->fields['tot_emails_lifetime']!='')?$r4->fields['tot_emails_lifetime']:0;
        //$tot_leads = $tot_call + $tot_email;
        //$tot_lifetime_leads = $tot_calls_lifetime + $tot_emails_lifetime;

        $sql_lifetime_leads = "SELECT * , tcalls+temails as total_leads
            FROM
            (SELECT 
                COUNT(DISTINCT  DATE_FORMAT(call_start,'%y-%m-%d') , gsm_number) as tcalls
                FROM calls 
            ) as a , 
            (SELECT 
                COUNT(*) as temails
                FROM emails 
            ) as b";
        $res_lifetime_leads = DashboardCommon::db()->Execute($sql_lifetime_leads);
        $tot_lifetime_leads = ($res_lifetime_leads->fields['total_leads']!='')?$res_lifetime_leads->fields['total_leads']:0;


        return array('total_calls'=>$tot_call, 'total_emails'=>$tot_email, 'total_leads' => $tot_leads,'total_calls_lifetime'=>$tot_calls_lifetime,'total_emails_lifetime'=>$tot_emails_lifetime,'total_leads_lifetime'=>$tot_lifetime_leads, 'query_total_leads'=>$sql_leads,'query_lifetime_leads'=>$sql_lifetime_leads );

    }

    public function getLeadsDataClient(){
        

        $tot_call = $tot_email = $tot_leads = 0;
        $sql1 = "SELECT count(*) as tot_calls FROM calls WHERE call_start >= '$this->from' AND call_start<='$this->to'
                AND gsm_number IN (".$this->get_gsm_number().") AND test_data=0 $this->calls_data_limit_clause
                ORDER BY call_start DESC";
        $r1 = DashboardCommon::db()->Execute($sql1);
        $sql2 = "SELECT count(*) as tot_email FROM emails WHERE email_date >= '$this->from' AND email_date<='$this->to'
                AND client_id IN ('".implode('\',\'', $this->get_unbounce_ids())."')  AND test_data=0
                ORDER BY email_date DESC";
        $r2 = DashboardCommon::db()->Execute($sql2);

        $sql_leads = "SELECT * ,tcalls+temails as total_leads
            FROM
            (SELECT 
            COUNT(DISTINCT  DATE_FORMAT(call_start,'%y-%m-%d') , gsm_number, callerid) as tcalls
            FROM calls 
            WHERE (call_start>='$this->from' AND call_start<='$this->to') AND gsm_number IN (".$this->get_gsm_number().")
                AND test_data=0 $this->calls_data_limit_clause
            ) as a , 
            (SELECT 
            COUNT(*) as temails
            FROM emails 
            WHERE (email_date>='$this->from' AND email_date<='$this->to') AND emails.client_id IN ('".implode('\',\'', $this->get_unbounce_ids())."')
                AND test_data=0
            ) as b";
        $r_sql_leads = DashboardCommon::db()->Execute($sql_leads);
        $tot_unique_calls = $r_sql_leads->fields['tcalls'];
        $tot_unique_emails = $r_sql_leads->fields['temails'];
        $tot_leads = $r_sql_leads->fields['total_leads'];

        $sql_calls_lifetime = "SELECT count(*) as tot_calls_lifetime FROM calls 
            WHERE gsm_number IN (".$this->get_gsm_number().") AND test_data=0 $this->calls_data_limit_clause";
        $r3 = DashboardCommon::db()->Execute($sql_calls_lifetime);
        $sql_emails_lifetime = "SELECT count(*) as tot_emails_lifetime FROM emails 
            WHERE client_id IN ('".implode('\',\'', $this->get_unbounce_ids())."')  AND test_data=0";
        $r4 = DashboardCommon::db()->Execute($sql_emails_lifetime);
        $tot_call = ($r1->fields['tot_calls']!='')?$r1->fields['tot_calls']:0;
        $tot_email = ($r2->fields['tot_email']!='')?$r2->fields['tot_email']:0;
        $tot_calls_lifetime = ($r3->fields['tot_calls_lifetime']!='')?$r3->fields['tot_calls_lifetime']:0;
        $tot_emails_lifetime = ($r4->fields['tot_emails_lifetime']!='')?$r4->fields['tot_emails_lifetime']:0;
        //$tot_leads = $tot_call + $tot_email;
        //$tot_lifetime_leads = $tot_calls_lifetime + $tot_emails_lifetime;

        $sql_lifetime_leads = "SELECT * , tcalls+temails as total_leads
            FROM
            (SELECT 
                COUNT(DISTINCT  DATE_FORMAT(call_start,'%y-%m-%d') , gsm_number, callerid) as tcalls
                FROM calls 
                WHERE gsm_number IN (".$this->get_gsm_number().") AND test_data=0 $this->calls_data_limit_clause
            ) as a , 
            (SELECT 
                COUNT(*) as temails
                FROM emails 
                WHERE client_id IN ('".implode('\',\'', $this->get_unbounce_ids())."')  AND test_data=0
            ) as b";
        $res_lifetime_leads = DashboardCommon::db()->Execute($sql_lifetime_leads);
        $tot_lifetime_leads = ($res_lifetime_leads->fields['total_leads']!='')?$res_lifetime_leads->fields['total_leads']:0;


        return array('total_calls'=>$tot_call, 'total_emails'=>$tot_email, 'total_leads' => $tot_leads,'total_calls_lifetime'=>$tot_calls_lifetime,'total_emails_lifetime'=>$tot_emails_lifetime,'total_leads_lifetime'=>$tot_lifetime_leads,'total_unique_calls'=>$tot_unique_calls,'total_unique_emails'=>$tot_unique_emails, 'query_leads'=>$sql_leads,'query_lifetime_leads'=>$sql_lifetime_leads );

    }

    public function getLeadsChartData($period){
        
        if(!DashboardCommon::is_su()){
            $client_calls_where = " AND gsm_number IN (".$this->get_gsm_number().") AND test_data=0
                                    $this->calls_data_limit_clause
                                    ORDER BY call_start DESC";
            $client_email_where = " AND client_id  IN ('".implode('\',\'', $this->get_unbounce_ids())."')  AND test_data=0
                                    ORDER BY email_date DESC";
        }

        $data = array();

        $period_days=array();
        $date_filter = "Y-m-d";

        if($period=='lifetime'){
            $period_days = getMonths($this->from, $this->to);
            $date_filter = "Y-m";
        }
        elseif($period=='last_30_days' || $period=='last_7_days' || $period=='yesterday' || $period=='month' ||
                $period=='daily' || $period=='today' || $period=='this_month' || $period=='custom' ||
                $period=='last_month'){
            $period_days = createDateRangeArray($this->from, $this->to);

        }else{
            $period_days = createDateRangeArray($this->from, $this->to);
        }

        if(!empty($period_days)){
            foreach($period_days as $date){
                if($date_filter==='Y-m-d')
                    $date_filtered = $date;
                else
                    $date_filtered = date_format($date,"$date_filter");
                $q1 = "SELECT count(*) as  total_calls FROM calls WHERE call_start LIKE '%".$date_filtered."%' $client_calls_where ";
                $rc = DashboardCommon::db()->Execute($q1);
                $c_total = $rc->fields['total_calls'];
                if($c_total==''){
                        $c_total=0;	
                }

                $q2 = "SELECT count(*) as  total_emails FROM emails WHERE email_date LIKE '%".$date_filtered."%' $client_email_where";
                $re = DashboardCommon::db()->Execute($q2);
                $e_total = $re->fields['total_emails'];
                if($e_total==''){
                        $e_total=0;	
                }

                $sql_leads = "SELECT * , tcalls+temails as total_leads
                    FROM
                    (SELECT 
                        COUNT(DISTINCT  DATE_FORMAT(call_start,'%y-%m-%d') , gsm_number) as tcalls
                        FROM calls 
                        WHERE
                        call_start LIKE '%".$date_filtered."%' $client_calls_where
                    ) as a , 
                    (SELECT 
                        COUNT(*) as temails
                        FROM emails 
                        WHERE
                        email_date LIKE '%".$date_filtered."%' $client_email_where
                    ) as b";

                //echo $sql_leads; die();
                $res_leads = DashboardCommon::db()->Execute($sql_leads);
                $tot_leads = ($res_leads->fields['total_leads']!='')?$res_leads->fields['total_leads']:0;

                $row = array('y'=>$date_filtered, 'a'=>$c_total, 'b'=>$e_total, 'c'=>$tot_leads);
                $data[] = $row;
            }
        }




        return $data;
    }
    
}

?>
