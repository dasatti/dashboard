<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Calls
 *
 * @author danish
 */

require_once dirname(__FILE__).'/DashboardCommon.php';

class Calls extends DashboardCommon{
    //put your code here
       
    private $period;
    
    private $to;
    
    private $from;
    
    private $calls_data_limit_clause = "";
    
    //array containing all the test gsm number for which recrods will not be shown to client
    private $test_numbers = array(); 
    
    public function __construct() {
        parent::__construct();
        
        $this->loadTestNumbers();
        
        $start_date_limit = $_SESSION['lm_auth']['campaign_start'];
        $end_date_limit = $_SESSION['lm_auth']['campaign_end'];
        if($start_date_limit!="" && $start_date_limit!="0000-00-00" && !DashboardCommon::is_su()){
            $this->calls_data_limit_clause = " AND call_start>='$start_date_limit'";
        }
        if($end_date_limit!="" && $end_date_limit!="0000-00-00" && !DashboardCommon::is_su()){
            $this->calls_data_limit_clause = " AND call_end<='$end_date_limit'";
        }
        
        if(!empty($this->test_numbers) && !DashboardCommon::is_su()){
            $gsm_numbers = explode(',', $this->get_gsm_number());
//             $this->calls_data_limit_clause .=" AND ".$this->get_gsm_number() ."
//                    NOT IN (". implode(',', $this->test_numbers) .")";
            foreach($gsm_numbers as $gsm){
                $this->calls_data_limit_clause .=" AND ".$gsm ."
                    NOT IN (". implode(',', $this->test_numbers) .")";
            }
        }
        
        $this->setPeriod('lifetime');
    }
    
    private function loadTestNumbers(){
        $sql = "SELECT phone_number FROM phone_numbers WHERE test_number=1";
        $r = DashboardCommon::db()->Execute($sql);
        while (!$r->EOF){
            $this->test_numbers[] = $r->fields['phone_number'];
            $r->MoveNext();
        }
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
        //add one to to include complete day range
        $this->to = date("Y-m-d", strtotime($this->to." +1 days"));
    }
    
    public function get_calls_data(){
        
        $where = "";
        
        if(!DashboardCommon::is_su()){
            
            return $this->get_calls_data_client();
            
            
            $where = " AND gsm_number IN( ".  $this->get_gsm_number()." )  AND test_data=0 $this->calls_data_limit_clause";
        }
        
        
        $arr_calls = createDateRangeArray($this->from,  $this->to);
        $json_array = array();
        foreach($arr_calls as $arr_call){
            $sql = "SELECT count(*) as total FROM calls ";
            $sql .= "WHERE call_start LIKE '%".$arr_call."%' $where"; 
            $res = DashboardCommon::db()->Execute($sql);
            $json_array[] = array('elapsed' => userdate($arr_call), 'value' => $res->fields['total']);

        }
        
        return $json_array;
        header('Content-Type: application/json');
        echo json_encode($json_array);
        die();   
    }
    
    public function get_calls_data_client(){
        require_once dirname(__FILE__).'/Client.php';
        $dates = DashboardCommon::createDateRangeArray($this->from, $this->to);
        $json_array = array();
        $campaigns = Client::get_campaigns();
        
        if(empty($campaigns))            return array();
        
        $json_array = array();
        foreach ($dates as $date){
            
            
            $union_array = array();
            foreach($campaigns as $campaign){
                $sql = "(SELECT * FROM calls WHERE gsm_number='".$campaign['gsm_number']."'";
                if($campaign['start_date']!='0000-00-00'){
                    //if($date < $campaign['start_date']) continue;
                    
                    $sql.=" AND call_start>='".$campaign['start_date']."'";
                }
                if($campaign['end_date']!='0000-00-00'){
                    //if($date > $campaign['end_date']) continue;
                    $sql.=" AND call_end<='".$campaign['end_date']."'";
                }
                $sql.=")";
                $union_array[] = $sql;
            }
            
            //if(empty($union_array)) return array();
            
            $inner_sql = implode(" UNION ", $union_array);
            
            $sql = "SELECT count(*) as total FROM ($inner_sql) as calls WHERE call_start LIKE '%$date%' AND test_data=0";
            
            $res = DashboardCommon::db()->Execute($sql);
            $json_array[] = array('elapsed' => userdate($date), 'value' => $res->fields['total']);
        }
        
        return $json_array;
    }


    public function get_calls_table_data(){
        
        
        $where = "";
        
        if(!DashboardCommon::is_su()){
            
            return $this->get_calls_table_data_client();
            $where = " AND gsm_number IN( ".$this->get_gsm_number()." )  AND test_data=0 $this->calls_data_limit_clause";
        }
        
        $json_array = array();
        
        $sql = "SELECT *, DATE_FORMAT(call_start,'%b %e, %Y') as call_date,
                DATE_FORMAT(call_start,'%h:%i %p') as call_time,
                IF( id IN (SELECT id FROM calls GROUP BY callerid,gsm_number HAVING MIN(call_start)),'New','') AS new_call
                FROM calls";
        $sql .= " WHERE call_start >= '".$this->from."' AND call_start<= '".$this->to."'
            $where ORDER BY call_start DESC ";//echo $sql;die;
        $r = DashboardCommon::db()->Execute($sql);
        foreach($r as $res){
            $json_array[] = $res;
        }

        return $json_array;
        header('Content-Type: application/json');
        echo json_encode($json_array);
        die();  
    }
    
    public function get_calls_table_data_client(){
        require_once dirname(__FILE__).'/Client.php';
        
        $campaigns = Client::get_campaigns();
        $first_campaign_start = Client::getFirstCampaignStartDate();
        
        if(empty($campaigns))            return array();
        
        $union_array = array();
        foreach($campaigns as $campaign){
            $sql = "(SELECT * FROM calls WHERE gsm_number='".$campaign['gsm_number']."'";
            if($campaign['start_date']!='0000-00-00'){
                //if($date < $campaign['start_date']) continue;

                $sql.=" AND call_start>='".$campaign['start_date']."'";
            }
            if($campaign['end_date']!='0000-00-00'){
                //if($date > $campaign['end_date']) continue;
                $sql.=" AND call_end<='".$campaign['end_date']."'";
            }
            $sql.=")";
            $union_array[] = $sql;
        }

        //if(empty($union_array)) return array();

        $inner_sql = implode(" UNION ", $union_array);
        
        
        $json_array = array();
        
        $campaign_start_limit = "";
        if($first_campaign_start!=''){
            $campaign_start_limit = "WHERE call_start>='$first_campaign_start'";
        }
        
        $sql = "SELECT *, DATE_FORMAT(call_start,'%b %e, %Y') as call_date,
                DATE_FORMAT(call_start,'%h:%i %p') as call_time,
                IF( id IN (SELECT id FROM calls $campaign_start_limit GROUP BY callerid HAVING MIN(call_start)),'New','') AS new_call
                FROM ($inner_sql) as calls";
        $sql .= " WHERE call_start >= '".$this->from."' AND call_start<= '".$this->to."'
            AND test_data=0 ORDER BY call_start DESC ";//echo $sql;die;
        $r = DashboardCommon::db()->Execute($sql);
        foreach($r as $res){
            $json_array[] = $res;
        }
        
        return $json_array;
    }
    
    function get_weekday_stats(){
        //only for super admin
        if(!DashboardCommon::is_su()) return null;
        
        $sat = strtotime("last saturday");
        $sat = date('w', $sat) == date('w') ? $sat + 7 * 86400 : $sat;
        $fri = strtotime(date("Y-m-d", $sat) . " +6 days");
        $from = date("Y-m-d", $sat);//for current week only
        $to = date("Y-m-d", $fri);//for current week only
        $sql = "SELECT DAYNAME(atr.call_start) as dayname,count(*) as total 
            FROM week_days wd 
            LEFT JOIN ( SELECT * FROM calls WHERE call_start >= '" . $this->from . "' AND call_start <= '" . $this->to . "') atr
            ON wd.week_day_num = DAYOFWEEK(atr.call_start)
            GROUP BY
            DAYOFWEEK(atr.call_start)";

        $this_week_rec = DashboardCommon::db()->Execute($sql);
        $saturday = $sunday =  $monday =  $tuesday =   $wednesday = 0;
        $thursday = $friday = 0;
//        $data = array();
//        while (!$this_week_rec->EOF) {
//            $k = $this_week_rec->fields['dayname'];
//            $data[$k]= $this_week_rec->fields['total'];
//            $this_week_rec->MoveNext();
//        }
//        
//        return array_keys($data, max($data));

        while (!$this_week_rec->EOF) {
            $daynames = $this_week_rec->fields['dayname'];
            $totalcalls = $this_week_rec->fields['total'];
            if ($daynames == 'Saturday') {
                $saturday = $this_week_rec->fields['total'];
            }
            if ($daynames == 'Sunday') {
                $sunday = $this_week_rec->fields['total'];
            }
            if ($daynames == 'Monday') {
                $monday = $this_week_rec->fields['total'];
            }
            if ($daynames == 'Tuesday') {
                $tuesday = $this_week_rec->fields['total'];
            }
            if ($daynames == 'Wednesday') {
                $wednesday = $this_week_rec->fields['total'];
            }
            if ($daynames == 'Thursday') {
                $thursday = $this_week_rec->fields['total'];
            }
            if ($daynames == 'Friday') {
                $friday = $this_week_rec->fields['total'];
            }

            $this_week_rec->MoveNext();
        }

        $arr = array('Saturday' => $saturday,
            'Sunday' => $sunday,
            'Monday' => $monday,
            'Tuesday' => $tuesday,
            'Wednesday' => $wednesday,
            'Thursday' => $thursday,
            'Friday' => $friday
        );
        $max_day = array_keys($arr, max($arr));
        
        $avg_calltime_sql = "SELECT sum(duration) as total_call_time, count(*) as total_records, 
            sum(duration) / count(*) as avg_time
            FROM 
            (
            SELECT call_end-call_start as duration
                    FROM calls
                    WHERE call_start >= '".$this->from."' AND call_start <= '".$this->to."'
            ) as dt";
        $avg_calltime_res = DashboardCommon::db()->Execute($avg_calltime_sql);
        
        
        return array('weekday'=>$max_day[0],'avg_call_time'=>$avg_calltime_res->fields['avg_time']);
        
    }
    
    function savePDFReport(){
        
        
        $report_data = $this->get_calls_table_data();
        
        $report_body = "<pre>".print_r($report_data,true)."</pre>";

        require dirname(__FILE__). '/../../libs/fpdf17/fpdf.php';
        $report_path = "../assets/tmp_reports/";
        $reportname = "calls_".  $this->get_gsm_number()."_".  $this->period.".pdf";

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(40,10,'Calls Reprot!');
        //$pdf->WriteHTML("<H2>gggg<H2>");
        //$pdf->Write(5, $report_body);
        
            $pdf->Ln();
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,10,'Caller Number');
        $pdf->Cell(40,10,'Call Start');
        $pdf->Cell(40,10,'Call End');
        $pdf->Cell(20,10,'Duration');
        $pdf->Cell(20,10,'Status');
            $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        foreach($report_data as $cd){
            if($cd['call_end']>0) $d =  "Successfully transferred";  
	    else $d="Busy";
            $pdf->Cell(40, 5, $cd['callerid']);
            $pdf->Cell(40, 5, $cd['call_start']);
            $pdf->Cell(40, 5, $cd['call_end']);
            $pdf->Cell(20, 5, $cd['total_duration']);
            $pdf->Cell(20, 5, $cd['status']);
            $pdf->Ln();
        }
        
        $pdf->Output($report_path.$reportname,'F');
        
        return $reportname;
    }
}

?>
