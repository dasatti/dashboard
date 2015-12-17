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
require_once dirname(__FILE__) . '/DashboardCommon.php';

class Leads extends DashboardCommon
{
    //put your code here

    private $period;
    private $to;
    private $from;
    private $calls_data_limit_clause = "";

    public function __construct()
    {
        parent::__construct();

        $start_date_limit = $_SESSION['lm_auth']['campaign_start'];
        $end_date_limit = $_SESSION['lm_auth']['campaign_end'];
        if ($start_date_limit != "" && $start_date_limit != "0000-00-00" && !DashboardCommon::is_su()) {
            $this->calls_data_limit_clause = " AND call_start>='$start_date_limit'";
        }
        if ($end_date_limit != "" && $end_date_limit != "0000-00-00" && !DashboardCommon::is_su()) {
            $this->calls_data_limit_clause = " AND call_end<='$end_date_limit'";
        }

        $this->setPeriod('lifetime');
    }

    public function setPeriod($period)
    {
        $this->period = $period;

        $rangeArray = DashboardCommon::getDateRangeFromPeriod($this->period);
        $this->from = $rangeArray['date_from'];
        $this->to = $rangeArray['date_to'];
    }

    public function setCustomPeriod($from, $to)
    {
        $this->period = 'custom';
        $from = strtotime($from);
        $to = strtotime($to);
        $this->from = date("Y-m-d", $from);
        $this->to = date("Y-m-d", $to);
        //add one to to include complete day range
        $this->to = date("Y-m-d", strtotime($this->to . " +1 days"));
    }

    public function getLeadsData()
    {
        if (!DashboardCommon::is_su()) {
            //not super admin
            return $this->getLeadsDataClient();
        }
        //only super admin below
        $tot_call = $tot_email = $tot_leads = 0;
        $sql1 = "SELECT count(*) as tot_calls FROM calls WHERE call_start >= '$this->from' AND call_start<='$this->to'
                ORDER BY call_start DESC";
        $tot_call = DashboardCommon::executeAndReturnSingleColResultAndCache($sql1, 'tot_calls');
        $sql2 = "SELECT count(*) as tot_email FROM emails WHERE email_date >= CONVERT_TZ('$this->from','+00:00','+04:00')
            AND email_date<=CONVERT_TZ('$this->to','+00:00','-04:00')
                ORDER BY email_date DESC";
        $tot_email = DashboardCommon::executeAndReturnSingleColResultAndCache($sql2, 'tot_email');

        $sql_leads = "SELECT * ,tcalls+temails as total_leads
            FROM
            (SELECT 
            /*COUNT(DISTINCT  /*DATE_FORMAT(call_start,'%y-%m-%d') ,// gsm_number) as tcalls*/
            COUNT(*) AS tcalls
            FROM calls 
            WHERE 
            
                id IN (  SELECT id FROM calls GROUP BY callerid HAVING MIN(call_start)  ) AND

                (call_start>='$this->from' AND call_start<='$this->to')
                ) as a , 
            (SELECT 
            COUNT(*) as temails
            FROM emails 
            WHERE (email_date>= CONVERT_TZ('$this->from','+00:00','-04:00')
                AND email_date<= CONVERT_TZ('$this->to','+00:00','-04:00') )
            ) as b";
        $tot_leads = DashboardCommon::executeAndReturnSingleColResultAndCache($sql_leads, 'total_leads');

        $sql_calls_lifetime = "SELECT count(*) as tot_calls_lifetime FROM calls";
        $tot_calls_lifetime = DashboardCommon::executeAndReturnSingleColResultAndCache($sql_calls_lifetime, 'tot_calls_lifetime');
        $sql_emails_lifetime = "SELECT count(*) as tot_emails_lifetime FROM emails";
        $tot_emails_lifetime = DashboardCommon::executeAndReturnSingleColResultAndCache($sql_emails_lifetime, 'tot_emails_lifetime');
        //$tot_leads = $tot_call + $tot_email;
        //$tot_lifetime_leads = $tot_calls_lifetime + $tot_emails_lifetime;

        $sql_lifetime_leads = "SELECT * , tcalls+temails as total_leads
            FROM
            (SELECT 
                COUNT(DISTINCT  /*DATE_FORMAT(call_start,'%y-%m-%d') ,*/ gsm_number) as tcalls
                FROM calls 
            ) as a , 
            (SELECT 
                COUNT(*) as temails
                FROM emails 
            ) as b";
        $tot_lifetime_leads = DashboardCommon::executeAndReturnSingleColResultAndCache($sql_lifetime_leads, 'total_leads');


        return array('total_calls' => $tot_call, 'total_emails' => $tot_email, 'total_leads' => $tot_leads, 'total_calls_lifetime' => $tot_calls_lifetime, 'total_emails_lifetime' => $tot_emails_lifetime, 'total_leads_lifetime' => $tot_lifetime_leads, 'query_total_leads' => $sql_leads, 'query_lifetime_leads' => $sql_lifetime_leads);
    }

    public function getLeadsDataClient()
    {

        $tot_call = $tot_email = $tot_leads = 0;
//        $sql1 = "SELECT count(*) as tot_calls FROM calls WHERE call_start >= '$this->from' AND call_start<='$this->to'
//                AND gsm_number IN (".$this->get_gsm_number().") AND test_data=0 $this->calls_data_limit_clause
//                ORDER BY call_start DESC";

        require_once dirname(__FILE__) . '/Client.php';
        $campaigns = Client::get_campaigns();
        $first_campaign_start = Client::getFirstCampaignStartDate();

        //if(empty($campaigns))            return array();

        $union_array = array();
        foreach ($campaigns as $campaign) {
            $sql = "(SELECT * FROM calls WHERE gsm_number='" . $campaign['gsm_number'] . "'";
            if ($campaign['start_date'] != '0000-00-00') {
                //if($date < $campaign['start_date']) continue;

                $sql.=" AND call_start>='" . $campaign['start_date'] . "'";
            }
            if ($campaign['end_date'] != '0000-00-00') {
                //if($date > $campaign['end_date']) continue;
                $sql.=" AND call_end<='" . $campaign['end_date'] . "'";
            }
            $sql.=") ";
            $union_array[] = $sql;
        }


        $inner_sql = implode(" UNION ", $union_array);

        if ($inner_sql == '') {
            $inner_sql = "(SELECT * FROM calls WHERE id = NULL) ";
        }

        $sql1 = "SELECT count(*) as tot_calls FROM ($inner_sql) AS calls WHERE call_start >= '$this->from' AND call_start<='$this->to'  AND test_data=0  ORDER BY call_start DESC";

        $tot_call = DashboardCommon::executeAndReturnSingleColResultAndCache($sql1, 'tot_calls');

        $sql2 = "SELECT count(*) as tot_email FROM emails WHERE
                email_date >= CONVERT_TZ('$this->from','+00:00','-04:00')
                AND email_date<= CONVERT_TZ('$this->to','+00:00','-04:00')
                AND client_id IN ('" . implode('\',\'', $this->get_unbounce_ids()) . "')  AND test_data=0
                ORDER BY email_date DESC";
        $tot_email = DashboardCommon::executeAndReturnSingleColResultAndCache($sql2, 'tot_email');


        $campaign_start_limit = "";
        if ($first_campaign_start != '') {
            $campaign_start_limit = "WHERE call_start>='$first_campaign_start'";
        }

        $sql_leads = "SELECT * ,tcalls+temails as total_leads
            FROM
            (SELECT 
            /*COUNT(DISTINCT  /*DATE_FORMAT(call_start,'%y-%m-%d') , // gsm_number, callerid) as tcalls*/
            COUNT(*) AS tcalls
            FROM ($inner_sql) AS calls 
            WHERE             
                id IN (  SELECT id FROM calls $campaign_start_limit GROUP BY callerid HAVING MIN(call_start)  ) AND                
                (call_start>='$this->from' AND call_start<='$this->to'
                ) AND test_data=0 
            ) as a , 
            (SELECT 
            COUNT(*) as temails
            FROM emails 
            WHERE (
                email_date>= CONVERT_TZ('$this->from' ,'+00:00','-04:00')
                AND email_date<= CONVERT_TZ('$this->to','+00:00','-04:00')
                    ) 
                AND emails.client_id IN ('" . implode('\',\'', $this->get_unbounce_ids()) . "')
                AND test_data=0
            ) as b"; //echo $sql_leads;die;
        $r_sql_leads = DashboardCommon::executeAndReturnSingleColResultAndCache($sql_leads, 'total_leads', false);
        $tot_unique_calls = $r_sql_leads->fields['tcalls'];
        $tot_unique_emails = $r_sql_leads->fields['temails'];
        $tot_leads = $r_sql_leads->fields['total_leads'];

        $sql_calls_lifetime = "SELECT count(*) as tot_calls_lifetime FROM ($inner_sql) AS calls 
            WHERE test_data=0 ";
        $tot_calls_lifetime = DashboardCommon::executeAndReturnSingleColResultAndCache($sql_calls_lifetime, 'tot_calls_lifetime');
        $sql_emails_lifetime = "SELECT count(*) as tot_emails_lifetime FROM emails 
            WHERE client_id IN ('" . implode('\',\'', $this->get_unbounce_ids()) . "')  AND test_data=0";
        $tot_emails_lifetime = DashboardCommon::executeAndReturnSingleColResultAndCache($sql_emails_lifetime, 'tot_emails_lifetime');
        //$tot_leads = $tot_call + $tot_email;
        //$tot_lifetime_leads = $tot_calls_lifetime + $tot_emails_lifetime;

        $sql_lifetime_leads = "SELECT * , tcalls+temails as total_leads
            FROM
            (SELECT 
                COUNT(DISTINCT  /*DATE_FORMAT(call_start,'%y-%m-%d') ,*/ gsm_number, callerid) as tcalls
                FROM ($inner_sql) AS calls 
                WHERE test_data=0
            ) as a , 
            (SELECT 
                COUNT(*) as temails
                FROM emails 
                WHERE client_id IN ('" . implode('\',\'', $this->get_unbounce_ids()) . "')  AND test_data=0
            ) as b";
        $tot_lifetime_leads = DashboardCommon::executeAndReturnSingleColResultAndCache($sql_lifetime_leads, '$sql_lifetime_leads');


        return array('total_calls' => $tot_call, 'total_emails' => $tot_email, 'total_leads' => $tot_leads, 'total_calls_lifetime' => $tot_calls_lifetime, 'total_emails_lifetime' => $tot_emails_lifetime, 'total_leads_lifetime' => $tot_lifetime_leads, 'total_unique_calls' => $tot_unique_calls, 'total_unique_emails' => $tot_unique_emails, 'query_leads' => $sql_leads, 'query_lifetime_leads' => $sql_lifetime_leads);
    }

    public function getLeadsChartData($period)
    {

        if (!DashboardCommon::is_su()) {
            return $this->getLeadsChartDataClient($period);
        }

        $data = array();

        $period_days = array();
        $date_filter = "Y-m-d";

        if ($period == 'lifetime') {
            $period_days = getMonths($this->from, $this->to);
            $date_filter = "Y-m";
        } elseif ($period == 'last_30_days' || $period == 'last_7_days' || $period == 'yesterday' || $period == 'month' ||
                $period == 'daily' || $period == 'today' || $period == 'this_month' || $period == 'custom' ||
                $period == 'last_month') {
            $period_days = createDateRangeArray($this->from, $this->to);
        } else {
            $period_days = createDateRangeArray($this->from, $this->to);
        }

        if (!empty($period_days)) {
            $callerGrpSql = "SELECT id FROM calls GROUP BY callerid HAVING MIN(call_start)";
            $callerSqlKey = DashboardCommon::generateMemcacheHash($callerGrpSql);
            $callerMemcachedData = DashboardCommon::getMemcacheData($callerSqlKey);
            if (!$callerMemcachedData) {
                $callerRes = DashboardCommon::db()->Execute($callerGrpSql);
                $callerRows = $callerRes->GetRows();
                $commaSepratedCallerIds = "";
                foreach ($callerRows as $row) {
                    if ($commaSepratedCallerIds)
                        $commaSepratedCallerIds .= "," . $row['id'];
                    else
                        $commaSepratedCallerIds .= $row['id'];
                }
                DashboardCommon::setMemcacheData($callerSqlKey, $callerMemcachedData);
            }
            else {
                $callerRes = $callerMemcachedData;
            }
            foreach ($period_days as $date) {
                if ($date_filter === 'Y-m-d')
                    $date_filtered = $date;
                else
                    $date_filtered = date_format($date, "$date_filter");
                $q1 = "SELECT count(*) as  total_calls FROM calls WHERE call_start LIKE '" . $date_filtered . "%' $client_calls_where ";
                $c_total = DashboardCommon::executeAndReturnSingleColResultAndCache($q1, 'total_calls');

                $q2 = "SELECT count(*) as  total_emails FROM emails WHERE 
                    CONVERT_TZ(email_date,'+00:00','+04:00') LIKE '" . $date_filtered . "%' $client_email_where";
                $e_total = DashboardCommon::executeAndReturnSingleColResultAndCache($q2, 'total_emails');

                $sql_leads = "SELECT * , tcalls+temails as total_leads
                    FROM
                    (SELECT 
                        /*COUNT(DISTINCT  /*DATE_FORMAT(call_start,'%y-%m-%d') ,// gsm_number) as tcalls*/
                        COUNT(*) AS tcalls
                        FROM calls 
                        WHERE
                        id IN ( $commaSepratedCallerIds  ) AND
                        /*call_start LIKE '%" . $date_filtered . "%' $client_calls_where*/
            call_start LIKE '" . $date_filtered . "%' $client_calls_where
                    ) as a , 
                    (SELECT 
                        COUNT(*) as temails
                        FROM emails 
                        WHERE
                        CONVERT_TZ(email_date,'+00:00','+04:00') LIKE '" . $date_filtered . "%' $client_email_where
                    ) as b";

                //echo $sql_leads; die();
                $tot_leads = DashboardCommon::executeAndReturnSingleColResultAndCache($sql_leads, 'total_leads');

                $row = array('y' => $date_filtered, 'a' => $c_total, 'b' => $e_total, 'c' => $tot_leads);
                $data[] = $row;
            }
        }

        return $data;
    }

    public function getLeadsChartDataClient($period)
    {
        require_once dirname(__FILE__) . '/Client.php';
        $campaigns = Client::get_campaigns();
        $first_campaign_start = Client::getFirstCampaignStartDate();

        //if(empty($campaigns))            return array();

        $union_array = array();
        foreach ($campaigns as $campaign) {
            $sql = "(SELECT * FROM calls WHERE gsm_number='" . $campaign['gsm_number'] . "'";
            if ($campaign['start_date'] != '0000-00-00') {
                //if($date < $campaign['start_date']) continue;

                $sql.=" AND call_start>='" . $campaign['start_date'] . "'";
            }
            if ($campaign['end_date'] != '0000-00-00') {
                //if($date > $campaign['end_date']) continue;
                $sql.=" AND call_end<='" . $campaign['end_date'] . "'";
            }
            $sql.=")";
            $union_array[] = $sql;
        }


        $inner_sql = implode(" UNION ", $union_array);

        if ($inner_sql == '') {
            $inner_sql = "(SELECT * FROM calls WHERE id = NULL)";
        }


        $client_calls_where = "  AND test_data=0 ORDER BY call_start DESC";
        $client_email_where = " AND client_id  IN ('" . implode('\',\'', $this->get_unbounce_ids()) . "')  AND test_data=0
                                ORDER BY email_date DESC";

        $data = array();

        $period_days = array();
        $date_filter = "Y-m-d";

        if ($period == 'lifetime') {
            $period_days = getMonths($this->from, $this->to);
            $date_filter = "Y-m";
        } elseif ($period == 'last_30_days' || $period == 'last_7_days' || $period == 'yesterday' || $period == 'month' ||
                $period == 'daily' || $period == 'today' || $period == 'this_month' || $period == 'custom' ||
                $period == 'last_month') {
            $period_days = createDateRangeArray($this->from, $this->to);
        } else {
            $period_days = createDateRangeArray($this->from, $this->to);
        }


        $campaign_start_limit = "";
        if ($first_campaign_start != '') {
            $campaign_start_limit = "WHERE call_start>='$first_campaign_start'";
        }


        if (!empty($period_days)) {
            foreach ($period_days as $date) {
                if ($date_filter === 'Y-m-d')
                    $date_filtered = $date;
                else
                    $date_filtered = date_format($date, "$date_filter");
                $q1 = "SELECT count(*) as  total_calls FROM ($inner_sql) AS calls WHERE call_start LIKE '%" . $date_filtered . "%' $client_calls_where ";
                $c_total = 0;
                $c_total = DashboardCommon::executeAndReturnSingleColResultAndCache($q1, 'total_calls');
                $q2 = "SELECT count(*) as total_emails FROM emails WHERE 
                    CONVERT_TZ(email_date,'+00:00','+04:00') LIKE '" . $date_filtered . "%' $client_email_where";
                $e_total = DashboardCommon::executeAndReturnSingleColResultAndCache($q2, 'total_emails');

                $sql_leads = "SELECT * , tcalls+temails as total_leads
                    FROM
                    (SELECT 
                        /*COUNT(DISTINCT  /*DATE_FORMAT(call_start,'%y-%m-%d') ,// gsm_number) as tcalls*/
                        COUNT(*) AS tcalls
                        FROM ($inner_sql) AS calls 
                        WHERE
                        id IN (  SELECT id FROM calls $campaign_start_limit GROUP BY callerid HAVING MIN(call_start)  ) AND
                        call_start LIKE '%" . $date_filtered . "%' $client_calls_where
                    ) as a , 
                    (SELECT 
                        COUNT(*) as temails
                        FROM emails 
                        WHERE
                        CONVERT_TZ(email_date,'+00:00','+04:00') LIKE '" . $date_filtered . "%' $client_email_where
                    ) as b";

                //echo $sql_leads; die();
                $tot_leads = DashboardCommon::executeAndReturnSingleColResultAndCache($q1, 'total_leads');

                $row = array('y' => $date_filtered, 'a' => $c_total, 'b' => $e_total, 'c' => $tot_leads);
                $data[] = $row;
            }
        }
        return $data;
    }

}
