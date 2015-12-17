<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SMS
 *
 * @author danish
 */


require_once dirname(__FILE__).'/DashboardCommon.php';



class SMS extends DashboardCommon{
    //put your code here
    
    private $period;
    
    private $to;
    
    private $from;
    
    private $sms_data_limit_clause = "";
    
    public function __construct() {
        parent::__construct();
        
        $start_date_limit = $_SESSION['lm_auth']['campaign_start'];
        $end_date_limit = $_SESSION['lm_auth']['campaign_end'];
        if($start_date_limit!="" && $start_date_limit!="0000-00-00" && !DashboardCommon::is_su()){
            $this->sms_data_limit_clause = " AND sms_dt>='$start_date_limit'";
        }
        if($end_date_limit!="" && $end_date_limit!="0000-00-00" && !DashboardCommon::is_su()){
            $this->sms_data_limit_clause = " AND email_date<='$end_date_limit'";
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
        //add one to to include complete day range
        $this->to = date("Y-m-d", strtotime($this->to." +1 days"));
    }
    
    public function get_sms_table_data(){
        $where = " WHERE 1 ";
        
        if(!DashboardCommon::is_su()){
            $where .= " AND gsm_number IN( ".$this->get_gsm_number()." ) 
                $this->sms_data_limit_clause ";
        }
        
        
        $where.=" AND sms_dt>='$this->from' AND sms_dt<='$this->to' ";
        
        
        $data_array = array();
        
        $sql = "SELECT * FROM sms $where ORDER BY sms_dt DESC ";
        $r = DashboardCommon::db()->Execute($sql);
        foreach($r as $res){
            $data_array[] = $res;
        }
        
        return $data_array;
    }
    
    public function get_sms_data(){
        $where = "";
        
        if(!DashboardCommon::is_su()){
            $where = " AND gsm_number IN( ".$this->get_gsm_number()." ) $this->sms_data_limit_clause
                ORDER BY sms_dt DESC";
        }
        
        
        $dates = createDateRangeArray($this->from,  $this->to);
        $data_array = array();
        
        foreach($dates as $date){
            $sql = "SELECT count(*) as total FROM sms ";
            $sql .= "WHERE sms_dt LIKE '%".$date."%' $where";// echo $sql;
            $res = DashboardCommon::db()->Execute($sql);
            $data_array[] = array('elapsed' => userdate($date), 'value' => $res->fields['total']);

        }
        
        return $data_array;
    }
    
    
    function savePDFReport(){
        
        
        $report_data = $this->get_sms_table_data();
        

        require dirname(__FILE__). '/../../libs/fpdf17/fpdf.php';
        $report_path = "../assets/tmp_reports/";
        $reportname = "sms_".  $this->get_gsm_number()."_".  $this->period.".pdf";

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(40,10,'SMS Report!');
        //$pdf->WriteHTML("<H2>gggg<H2>");
        //$pdf->Write(5, $report_body);
        
            $pdf->Ln();
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(30,10,'Caller Number');
        $pdf->Cell(30,10,'Forward Number');
        $pdf->Cell(40,10,'Date');
        $pdf->MultiCell(80,10,'Message');
            $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        foreach($report_data as $cd){
            $pdf->Cell(30, 5, $cd['callerid']);
            $pdf->Cell(30, 5, $cd['forward_number']);
            $pdf->Cell(40, 5, $cd['sms_dt']);
            $pdf->MultiCell(80, 5, $cd['sms']);
            $pdf->Ln();
        }
        
        $pdf->Output($report_path.$reportname,'F');
        
        return $reportname;
    }
    
}

?>
