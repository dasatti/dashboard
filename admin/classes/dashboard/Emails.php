<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Emails
 *
 * @author danish
 */
ini_set('display_errors', 0); 
error_reporting(E_ALL);


require_once dirname(__FILE__).'/DashboardCommon.php';


class Emails extends DashboardCommon{
    //put your code here
    
    private $period;
    
    private $to;
    
    private $from;
    
    
    public function __construct() {
        parent::__construct();
        
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
        //add one to to include complete day range
        $this->to = date("Y-m-d", strtotime($this->to." +1 days"));
    }
    
    
    public function get_emails_data(){
        
        $where = "";
        
        if(!DashboardCommon::is_su()){
            //$where = " AND emails.client_id='".$this->get_unbounce_id()."'  AND test_data=0 
                //ORDER BY email_date DESC";
            $where = " AND emails.client_id IN ('".implode('\',\'', $this->get_unbounce_ids())."')
                AND test_data=0 ORDER BY email_date DESC";
        }
        
        
        
        $dates = createDateRangeArray($this->from,  $this->to);
        $data_array = array();
        foreach($dates as $date){
            $sql = "SELECT count(*) as total FROM emails ";
            $sql .= "WHERE CONVERT_TZ(email_date,'+00:00','+04:00') LIKE '%".$date."%' $where";// echo $sql;
            $res = DashboardCommon::db()->Execute($sql);
            $data_array[] = array('elapsed' => userdate($date), 'value' => $res->fields['total']);

        }
        
        return $data_array;
    }
    
    public function get_email_table_data(){
        
        $where = "";
        
        if(!DashboardCommon::is_su()){
            //$where = " AND emails.client_id='".$this->get_unbounce_id()."'  AND test_data=0 ";
            $where = " AND emails.client_id IN ('".implode('\',\'', $this->get_unbounce_ids())."')  AND test_data=0 ";
        }
        
        
        $data_array = array();
        
        $sql = "SELECT emails . *,CONVERT_TZ(email_date,'+00:00','+04:00') AS email_date_ae,
            campaign_name, tbl_admin.name AS client_name
            FROM emails
            LEFT JOIN campaigns ON campaigns.unbounce_id=emails.client_id
            LEFT JOIN tbl_admin ON tbl_admin.id = campaigns.client_id";
        $sql .= " WHERE email_date >= CONVERT_TZ('".$this->from."','+00:00','-04:00')
            AND email_date <= CONVERT_TZ('".$this->to."','+00:00','-04:00')
            $where ORDER BY email_date DESC ";//echo $sql;die;
        $r = DashboardCommon::db()->Execute($sql);
        foreach($r as $res){
            $data_array[] = $res;
        }

        return $data_array;
    }
    
    public function get_email_chart_data(){
        $where = "";
        
        if(!DashboardCommon::is_su()){
            $where = " AND client_id  IN ('".implode('\',\'', $this->get_unbounce_ids())."') AND test_data=0";
        }
        
        $rangeArray = getDateRangeFromPeriod($this->period);
        $date_from = $rangeArray['date_from'];
        $date_to = $rangeArray['date_to'];
        
        $where.=" AND email_date>=CONVERT_TZ('$date_from','+00:00','-04:00') AND
            email_date<=CONVERT_TZ('$date_to','+00:00','-04:00') ";
        
        
        $data_array = array();
        
        $sql = "SELECT *,CONVERT_TZ(email_date,'+00:00','+04:00') AS email_date_ae
            FROM emails WHERE 1 $where ORDER BY email_date DESC ";
        $r = DashboardCommon::db()->Execute($sql);
        foreach($r as $res){
            $data_array[] = $res;
        }
        
        return $data_array;
    }
    
    function get_weekday_stats(){
        //only for super admin
        if(!DashboardCommon::is_su()) return null;
        
        $sat = strtotime("last saturday");
        $sat = date('w', $sat) == date('w') ? $sat + 7 * 86400 : $sat;
        $fri = strtotime(date("Y-m-d", $sat) . " +6 days");
        $from = date("Y-m-d", $sat);//for current week only
        $to = date("Y-m-d", $fri);//for current week only
        $sql = "SELECT DAYNAME(atr.email_date) as dayname,count(*) as total 
            FROM week_days wd 
            LEFT JOIN ( SELECT * FROM emails WHERE email_date >= CONVERT_TZ('" . $this->from . "','+00:00','-04:00')
                AND email_date <= CONVERT_TZ('" . $this->to . "','+00:00','-04:00') ) atr
            ON wd.week_day_num = DAYOFWEEK(atr.email_date)
            GROUP BY
            DAYOFWEEK(atr.email_date)";

        $this_week_rec = DashboardCommon::db()->Execute($sql);
        $saturday = $sunday =  $monday =  $tuesday =   $wednesday = 0;
        $thursday = $friday = 0;

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
        
        
        //Peak time
        $sql_peak = "SELECT EXTRACT(hour FROM CONVERT_TZ(email_date,'+00:00','+04:00')) as hour,count(*)
            FROM emails  GROUP BY EXTRACT(hour FROM CONVERT_TZ(email_date,'+00:00','+04:00'))
            ORDER BY count(*) DESC LIMIT 1";
        $res_peak = DashboardCommon::db()->Execute($sql_peak);
        $peak_time_f = $res_peak->fields['hour'];
        $peak_time_t = $peak_time_f + 1;
        $today = new DateTime('NOW');
        $today->setTime($peak_time_f, 0, 0);
        $today2 = new DateTime('NOW');
        $today2->setTime($peak_time_t, 0, 0);
        $peak_time = $today->format('H A')." - ".$today2->format('H A');
        
        // Average Emails per month
        $sql_avg_month = "select monthname(email_date) email_date,count(*) as total_sum 
            FROM emails GROUP BY monthname(email_date)";
        $res_avg_month = DashboardCommon::db()->Execute($sql_avg_month);
        $avg_total = $res_avg_month->recordCount();
        $total_sum = 0;
        while(!$res_avg_month->EOF){

                $total_sum+=$res_avg_month->fields['total_sum'];
        $res_avg_month->MoveNext();
        }
        $avg_per_month = ceil($total_sum/$avg_total);
        
        return array('average'=>$avg_per_month,'weekday'=>$max_day[0],'peak_time'=>$peak_time);
        
    }
    
    function savePDFReport(){
        
        
        $report_data = $this->get_email_table_data();
        
        //$report_body = "<pre>".print_r($report_data,true)."</pre>";

        require dirname(__FILE__). '/../../libs/fpdf17/fpdf.php';
        $report_path = "../assets/tmp_reports/";
        $reportname = "emails_".  $this->get_gsm_number()."_".  $this->period.".pdf";

        $pdf = new FPDF();
        $pdf->AddPage('L');
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(40,10,'Emails Reprot!');
        //$pdf->WriteHTML("<H2>gggg<H2>");
        //$pdf->Write(5, $report_body);
        
            $pdf->Ln();
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,10,'Name');
        $pdf->Cell(50,10,'Email');
        $pdf->Cell(40,10,'Phone');
        $pdf->Cell(40,10,'Date');
        $pdf->MultiCell(100,10,'Message');
            $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        foreach($report_data as $cd){
            
            $pdf->Cell(40, 5, $cd['name']);
            $pdf->Cell(50, 5, $cd['email']);
            $pdf->Cell(40, 5, $cd['phone']);
            $pdf->Cell(40, 5, $cd['email_date']);
            $pdf->MultiCell(100, 5, $cd['message']);
            $pdf->Ln();
        }
        
        $pdf->Output($report_path.$reportname,'F');
        
        return $reportname;
    }
    
}

?>
