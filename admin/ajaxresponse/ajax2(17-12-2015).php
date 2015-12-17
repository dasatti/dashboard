<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
ini_set("display_error", 1);
error_reporting(E_ALL);
require_once('../../adodb/adodb.inc.php');
require_once('../../include/siteconfig.inc.php');
require_once('../../include/sitefunction.php');
require_once('../include/common.php');

$db = ADONewConnection('mysqli');
$db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");
	
//Check if user is super admin
$is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
$client_id;

$client_id_temp = $_SESSION['lm_auth']['client_id'];
if(!empty($client_id_temp)){
        $client_id_arr = explode("#",$client_id_temp);

    foreach($client_id_arr as $client_id_single){
            $client_ids.=$client_id_single.',';	
    }
    $client_id = rtrim($client_ids,',');
}
$client_gsm_id = $client_id;
$client_unbouce_id = $_SESSION['lm_auth']['tbl_id'];


//restrict data fetching within curent campaign period
$start_date_limit = $_SESSION['lm_auth']['campaign_start'];
$end_date_limit = $_SESSION['lm_auth']['campaign_end'];
$calls_data_limit_clause = "";
$sms_data_limit_clause = "";
$email_data_limit_clause = "";
if($start_date_limit!="" && $start_date_limit!="0000-00-00" && !$is_su){
    $calls_data_limit_clause = " AND call_start>='$start_date_limit'";
    $sms_data_limit_clause = " AND sms_dt>='$start_date_limit'";
    $email_data_limit_clause = " AND email_date>='$start_date_limit'";
}
if($end_date_limit!="" && $end_date_limit!="0000-00-00" && !$is_su){
    $calls_data_limit_clause = " AND call_end<='$end_date_limit'";
    $sms_data_limit_clause = " AND sms_dt<='$end_date_limit'";
    $email_data_limit_clause = " AND email_date<='$end_date_limit'";
}



//implement test numbers
$tn_sql = "SELECT phone_number FROM phone_numbers WHERE test_number=1";
$tn_r = $db->Execute($tn_sql);
$test_numbers = array();
while (!$tn_r->EOF){
    $test_numbers[] = $tn_r->fields['phone_number'];
    $tn_r->MoveNext();
}
if(!empty($test_numbers) && !$is_su){
    $calls_data_limit_clause .=" AND ".$client_gsm_id ."
        NOT IN (". implode(',', $test_numbers) .")";
}




function getAdminEmails(){
    global $db;
    $sql = "SELECT email FROM tbl_admin WHERE account_type='super'";
    $r = $db->Execute($sql);
    $emails = array();
    if($r->RecordCount()>0){
        while(!$r->EOF){
            $emails[] = $r->fields['email'];
            $r->MoveNext();
        }
    }
    return $emails;
}

function getLeadsData($from,$to){
    if(!$_SESSION['lm_auth']['account_type']=='super'){
        //no super admin
        return array();
    }
    //only super admin below
    global $db;
    $tot_call = $tot_email = $tot_leads = 0;
    $sql1 = "SELECT count(*) as tot_calls FROM calls WHERE call_start >= '$from' AND call_start<='$to'
            ORDER BY call_start DESC";
    $r1 = $db->Execute($sql1);
    $sql2 = "SELECT count(*) as tot_email FROM emails WHERE email_date >= '$from' AND email_date<='$to'
            ORDER BY email_date DESC";
    $r2 = $db->Execute($sql2);
    
    $sql_leads = "SELECT * ,tcalls+temails as total_leads
        FROM
        (SELECT 
        COUNT(DISTINCT  DATE_FORMAT(call_start,'%y-%m-%d') , gsm_number) as tcalls
        FROM calls 
        WHERE (call_start>='$from' AND call_start<='$to')
        ) as a , 
        (SELECT 
        COUNT(*) as temails
        FROM emails 
        WHERE (email_date>='$from' AND email_date<='$to')
        ) as b";
    $r_sql_leads = $db->Execute($sql_leads);
    $tot_leads = $r_sql_leads->fields['total_leads'];
    
    $sql_calls_lifetime = "SELECT count(*) as tot_calls_lifetime FROM calls";
    $r3 = $db->Execute($sql_calls_lifetime);
    $sql_emails_lifetime = "SELECT count(*) as tot_emails_lifetime FROM emails";
    $r4 = $db->Execute($sql_emails_lifetime);
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
    $res_lifetime_leads = $db->Execute($sql_lifetime_leads);
    $tot_lifetime_leads = ($res_lifetime_leads->fields['total_leads']!='')?$res_lifetime_leads->fields['total_leads']:0;
    
    
    return array('total_calls'=>$tot_call, 'total_emails'=>$tot_email, 'total_leads' => $tot_leads,'total_calls_lifetime'=>$tot_calls_lifetime,'total_emails_lifetime'=>$tot_emails_lifetime,'total_leads_lifetime'=>$tot_lifetime_leads, 'query_total_leads'=>$sql_leads,'query_lifetime_leads'=>$sql_lifetime_leads );
    
}

function getLeadsDataClient($from,$to){
    global $db;
    global $client_gsm_id ;
    global $client_unbouce_id;
    global $calls_data_limit_clause;
    
    
    $tot_call = $tot_email = $tot_leads = 0;
    $sql1 = "SELECT count(*) as tot_calls FROM calls WHERE call_start >= '$from' AND call_start<='$to'
            AND gsm_number IN ($client_gsm_id) AND test_data=0 $calls_data_limit_clause
            ORDER BY call_start DESC";
    $r1 = $db->Execute($sql1);
    $sql2 = "SELECT count(*) as tot_email FROM emails WHERE email_date >= '$from' AND email_date<='$to'
            AND client_id='$client_unbouce_id' AND test_data=0
            ORDER BY email_date DESC";
    $r2 = $db->Execute($sql2);
    
    $sql_leads = "SELECT * ,tcalls+temails as total_leads
        FROM
        (SELECT 
        COUNT(DISTINCT  DATE_FORMAT(call_start,'%y-%m-%d') , gsm_number, callerid) as tcalls
        FROM calls 
        WHERE (call_start>='$from' AND call_start<='$to') AND gsm_number IN ($client_gsm_id)
            AND test_data=0 $calls_data_limit_clause
        ) as a , 
        (SELECT 
        COUNT(*) as temails
        FROM emails 
        WHERE (email_date>='$from' AND email_date<='$to') AND client_id='$client_unbouce_id'
            AND test_data=0
        ) as b";
    $r_sql_leads = $db->Execute($sql_leads);
    $tot_unique_calls = $r_sql_leads->fields['tcalls'];
    $tot_unique_emails = $r_sql_leads->fields['temails'];
    $tot_leads = $r_sql_leads->fields['total_leads'];
    
    $sql_calls_lifetime = "SELECT count(*) as tot_calls_lifetime FROM calls 
        WHERE gsm_number IN ($client_gsm_id) AND test_data=0 $calls_data_limit_clause";
    $r3 = $db->Execute($sql_calls_lifetime);
    $sql_emails_lifetime = "SELECT count(*) as tot_emails_lifetime FROM emails 
        WHERE client_id='$client_unbouce_id' AND test_data=0";
    $r4 = $db->Execute($sql_emails_lifetime);
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
            WHERE gsm_number IN ($client_gsm_id) AND test_data=0 $calls_data_limit_clause
        ) as a , 
        (SELECT 
            COUNT(*) as temails
            FROM emails 
            WHERE client_id='$client_unbouce_id' AND test_data=0
        ) as b";
    $res_lifetime_leads = $db->Execute($sql_lifetime_leads);
    $tot_lifetime_leads = ($res_lifetime_leads->fields['total_leads']!='')?$res_lifetime_leads->fields['total_leads']:0;
    
    
    return array('total_calls'=>$tot_call, 'total_emails'=>$tot_email, 'total_leads' => $tot_leads,'total_calls_lifetime'=>$tot_calls_lifetime,'total_emails_lifetime'=>$tot_emails_lifetime,'total_leads_lifetime'=>$tot_lifetime_leads,'total_unique_calls'=>$tot_unique_calls,'total_unique_emails'=>$tot_unique_emails, 'query_leads'=>$sql_leads,'query_lifetime_leads'=>$sql_lifetime_leads );
    
}

function getLeadsChartData($from,$to,$period){
    global $db;
    global $is_su;
    global $client_gsm_id ;
    global $client_unbouce_id;
    global $calls_data_limit_clause;
    
    if(!$is_su){
        $client_calls_where = " AND gsm_number IN ($client_gsm_id) AND test_data=0
                                $calls_data_limit_clause
                                ORDER BY call_start DESC";
        $client_email_where = " AND client_id = '$client_unbouce_id' AND test_data=0
                                ORDER BY email_date DESC";
    }
    
    $data = array();
    
    $period_days=array();
    $date_filter = "Y-m-d";
    
    if($period=='lifetime'){
        $period_days = getMonths($from, $to);
        $date_filter = "Y-m";
    }
    elseif($period=='last_30_days' || $period=='last_7_days' || $period=='yesterday' || $period=='month' ||
            $period=='daily' || $period=='today' || $period=='this_month' || $period=='custom' ||
            $period=='last_month'){
        $period_days = createDateRangeArray($from, $to);
        
    }else{
        $period_days = createDateRangeArray($from, $to);
    }
    
    if(!empty($period_days)){
        foreach($period_days as $date){
            if($date_filter==='Y-m-d')
                $date_filtered = $date;
            else
                $date_filtered = date_format($date,"$date_filter");
            $q1 = "SELECT count(*) as  total_calls FROM calls WHERE call_start LIKE '%".$date_filtered."%' $client_calls_where ";
            $rc = $db->Execute($q1);
            $c_total = $rc->fields['total_calls'];
            if($c_total==''){
                    $c_total=0;	
            }

            $q2 = "SELECT count(*) as  total_emails FROM emails WHERE email_date LIKE '%".$date_filtered."%' $client_email_where";
            $re = $db->Execute($q2);
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
            $res_leads = $db->Execute($sql_leads);
            $tot_leads = ($res_leads->fields['total_leads']!='')?$res_leads->fields['total_leads']:0;

            $row = array('y'=>$date_filtered, 'a'=>$c_total, 'b'=>$e_total, 'c'=>$tot_leads);
            $data[] = $row;
        }
    }
    
    
    
    
    return $data;
}


////////////////////////////////////////////////////////////////////////////
// ROI
////////////////////////////////////////////////////////////////////////////
function getROIData($from,$to){
    
    if($is_su){
        //admin roi not supported yet
        return array();
    }
    
    global $db;
    global $client_gsm_id;
    
    $period_leads = getLeadsDataClient($from,$to);
    $lifetime_range = getDateRangeFromPeriod('lifetime');
    $lifetime_from = $lifetime_range['date_from'];
    $lifetime_to = $lifetime_range['date_to'];
    $lifetime_leads = getLeadsDataClient($lifetime_from,$lifetime_to);
    
    $total_leads_period = $period_leads['total_leads'];
    $total_leads_lifetime = $lifetime_leads['total_leads'];
    
    $sql_roi2 = "SELECT avg_value_of_sale,avg_lead_to_sale FROM tbl_admin WHERE client_id LIKE '%".$client_gsm_id."%'";
    $res_roi2 = $db->Execute($sql_roi2);
    $avg_value_of_sale = ($res_roi2->fields['avg_value_of_sale']!='')?$res_roi2->fields['avg_value_of_sale']:0;
    $avg_lead_to_sale = ($res_roi2->fields['avg_lead_to_sale']!='')?$res_roi2->fields['avg_lead_to_sale']:0;
    
    $period_roi = $avg_value_of_sale * ($total_leads_period*($avg_lead_to_sale/100));
    $lifetime_roi = $avg_value_of_sale * ($total_leads_lifetime*($avg_lead_to_sale/100));
    
    //return array('period_roi'=>$period_roi,'lifetime_roi'=>$lifetime_roi,'total_leads_period'=>$total_leads_period,'avg_value_of_sale'=>$avg_value_of_sale,'avg_lead_to_sale'=>$avg_lead_to_sale,'period_leads'=>$period_leads);
    return array('period_roi'=>$period_roi,'lifetime_roi'=>$lifetime_roi,'total_leads_period'=>$total_leads_period,'avg_value_of_sale'=>$avg_value_of_sale,'avg_lead_to_sale'=>$avg_lead_to_sale);
}


/////////////////////////////////////////////////////////////////////////////////
// Generate PDF Report
/////////////////////////////////////////////////////////////////////////////////

function generateCallReportPDF($from,$to){
    
    require '../libs/fpdf17/fpdf.php';
    
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(40,10,'Hello World!');
    $pdf->Output("../assets/tmp_reports/share.pdf",'F');
}


if(isset($_GET['act'])){
    
    ////////////////////////////////////////////////////////////////////////////
    // Recommend Us Model
    /////////////////////////////////////////////////////////////////////////////
    if($_GET['act']=='share'){
        $to = $_POST['to'];
        $res = array();
        
        
        if(!filter_var($to, FILTER_VALIDATE_EMAIL)){
            $res['type'] = 'error';
            $res['msg'] = 'Invalid Email';
            echo json_encode($res);
            die;
        }
        
        $message = $_POST['message'];
        if(trim($message)==''){
            $res['type'] = 'error';
            $res['msg'] = 'Message could not be empty';
            echo json_encode($res);
            die;
        }
        
        
        //generate report to sahre
        $report_path = '';
        $report_type=$_POST['share_report_type'];
        if($report_type=='calls'){
            $report_period = $_POST['share_report_period'];
            if(trim($report_period)==''){
                $report_period='lifetime';
            }
            
            require '../classes/dashboard/Calls.php';
            $calls = new Calls();
            $calls->setPeriod($report_period);
            if($report_period=='custom'){
                $calls->setCustomPeriod($_POST['from'], $_POST['to']);
            }
            $report_file = $calls->savePDFReport();
        } elseif($report_type=='emails'){
            $report_period = $_POST['share_report_period'];
            if(trim($report_period)==''){
                $report_period='lifetime';
            }
            require '../classes/dashboard/Emails.php';
            $emails = new Emails();
            $emails->setPeriod($report_period);
            if($report_period=='custom'){
                $calls->setCustomPeriod($_POST['from'], $_POST['to']);
            }
            $report_file = $emails->savePDFReport();
        } elseif($report_type=='sms'){
            $report_period = $_POST['share_report_period'];
            if(trim($report_period)==''){
                $report_period='lifetime';
            }
            require '../classes/dashboard/SMS.php';
            $sms = new SMS();
            $sms->setPeriod($report_period);
            if($report_period=='custom'){
                $sms->setCustomPeriod($_POST['from'], $_POST['to']);
            }
            $report_file = $sms->savePDFReport();
        }
        
        
        
        $admins = getAdminEmails();
        $admin_emails = implode(',', $admins);
        $name = $_SESSION['lm_auth']['name'];
        //mail($to, 'New Report shared by '.$name.' with you', $message);
        //inform to admin also
        $admin_msg = "Follwing localmedia report was shared by $name to $to.\n\n";
        $admin_msg.= "-------------------------------------------------\n\n";
        $admin_msg.= $message;
        $admin_msg.= "\n\n-------------------------------------------------\n\n";
        //mail($admin_emails, 'New Report shared by '.$name, $admin_msg);
        
        require '../libs/php-mailer/class.phpmailer.php';
        $email = new PHPMailer();
        $email->From      = 'admin@localmedia.ae';
        $email->FromName  = 'Localmedia Dashboard';
        $email->Subject   = 'New Report shared by '.$name.' with you';
        $email->Body      = $message;
        $email->AddAddress( $to );
        $email->AddAddress( $admin_emails );
        $report_path = dirname(__FILE__). '/../assets/tmp_reports/';//.$report_path;
        if($report_file!='')
            $email->AddAttachment( $report_path.$report_file );
        $sent = $email->Send();
        
        
        
        $res['type'] = 'success';
        $res['msg'] = 'Successfully Shared with attachment '.$report_path.$report_file;
        $res['name'] = $name;
        $res['report_path'] = $report_path;
        echo json_encode($res);
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // Get Calls Data
    ////////////////////////////////////////////////////////////////////////////
    
    if($_GET['act']=='get_calls_data'){
        
        
        if(!isset($_GET['period'])) $period = 'lifetime';
        else $period = $_GET['period'];
        
        //Class based implementation
        require '../classes/dashboard/Calls.php';
        $calls = new Calls();
        $calls->setPeriod($period);
        if($period=='custom'){
            $calls->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        $data = $calls->get_calls_data();
        //$data = array('hello');
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
        
        
        //non class based implementation
        $where = "";
        
        if(!$is_su){
            $where = " AND gsm_number IN( ".$client_id." )  AND test_data=0 $calls_data_limit_clause";
        }
        
        $rangeArray = getDateRangeFromPeriod($period);
        $calls_date_from = $rangeArray['date_from'];
        $calls_date_to = $rangeArray['date_to'];
        $arr_calls = createDateRangeArray($calls_date_from,$calls_date_to);
        $json_array = array();
        foreach($arr_calls as $arr_call){
            $sql = "SELECT count(*) as total FROM calls ";
            $sql .= "WHERE call_start LIKE '%".$arr_call."%' $where"; 
            $res = $db->Execute($sql);
            $json_array[] = array('elapsed' => userdate($arr_call), 'value' => $res->fields['total']);

        }
        
        header('Content-Type: application/json');
        echo json_encode($json_array);
        die();     
        
        
    }
    
    
    if($_GET['act']=='get_calls_table_data'){
        
        if(!isset($_GET['period'])) $period = 'lifetime';
        else $period = $_GET['period'];
        
        //class based implementation
        require '../classes/dashboard/Calls.php';
        $calls = new Calls();
        $calls->setPeriod($period);
        if($period=='custom'){
            $calls->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }//echo $period,$_REQUEST['from'], $_REQUEST['to'];die;
        $data = $calls->get_calls_table_data();
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
        
        //non class base implementation
        $where = "1";
        
        if(!$is_su){
            $where = " AND gsm_number IN( ".$client_id." )  AND test_data=0 $calls_data_limit_clause";
        }
        
        
        $rangeArray = getDateRangeFromPeriod($period);
        $calls_date_from = $rangeArray['date_from'];
        $calls_date_to = $rangeArray['date_to'];
        
        
        $json_array = array();
        
        $sql = "SELECT * FROM calls WHERE $where ORDER BY call_start DESC ";
        $r = $db->Execute($sql);
        foreach($r as $res){
            $json_array[] = $res;
        }

        
        header('Content-Type: application/json');
        echo json_encode($json_array);
        die();    
    }
    
    if($_GET['act']=='get_calls_table_data_dt'){
        
        if(!isset($_GET['period'])) $period = 'lifetime';
        else $period = $_GET['period'];
        
        //class based implementation
        require '../classes/dashboard/Calls.php';
        $calls = new Calls();
        $calls->setPeriod($period);
        if($period=='custom'){
            $calls->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }//echo $period,$_REQUEST['from'], $_REQUEST['to'];die;
        $data = array();
        $data['draw']=1;
        $data['recordsTotal']=100;
        $data['recordsFiltered']=100;
        $data['data'] = $calls->get_calls_table_data();
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
    }
    
    if($_GET['act']=='get_calls_data_weekday'){
        if(!isset($_GET['period'])) $period = 'lifetime';
        else $period = $_GET['period'];
        
        require '../classes/dashboard/Calls.php';
        $calls = new Calls();
        $calls->setPeriod($period);
        if($period=='custom'){
            $calls->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        $data = $calls->get_weekday_stats();
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    // Get leads statistics data
    ////////////////////////////////////////////////////////////////////////////
    if($_GET['act']==='get_leads_data'){
        if(!isset($_GET['period'])) $period = 'lifetime';
        else $period = $_GET['period'];
        
        
        
        //class based implementation
        require '../classes/dashboard/Leads.php';
        $leads = new Leads();
        $leads->setPeriod($period);
        if($period=='custom'){
            $leads->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        
        header('Content-Type: application/json');
        if($is_su) echo json_encode($leads->getLeadsData());
        else echo json_encode($leads->getLeadsDataClient());
        die();
        
        //non class based implementation
        
        $rangeArray = getDateRangeFromPeriod($period);
        $calls_date_from = $rangeArray['date_from'];
        $calls_date_to = $rangeArray['date_to'];
        
        header('Content-Type: application/json');
        if($is_su) echo json_encode(getLeadsData($calls_date_from, $calls_date_to));
        else echo json_encode(getLeadsDataClient($calls_date_from, $calls_date_to));
        die(); 
    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    // Get leads chart data
    ////////////////////////////////////////////////////////////////////////////
    if($_GET['act']==='get_leads_chart_data'){
        if(!isset($_GET['period'])) $period = 'lifetime';
        $period = $_GET['period'];
        
        
        //class based implementation
        require '../classes/dashboard/Leads.php';
        $leads = new Leads();
        $leads->setPeriod($period);
        if($period=='custom'){
            $leads->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($leads->getLeadsChartData($period));
        die();
        
        //non class based implementation
        
        $rangeArray = getDateRangeFromPeriod($period);
        $leads_date_from = $rangeArray['date_from'];
        $leads_date_to = $rangeArray['date_to'];
        
        header('Content-Type: application/json');
        echo json_encode(getLeadsChartData($leads_date_from, $leads_date_to,$period));
        die();
        
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // Emails
    ////////////////////////////////////////////////////////////////////////////

    if($_GET['act']=='get_emails_data'){
        
        if(!isset($_GET['period'])) $period = 'lifetime';
        $period = $_GET['period'];
        
        //class based implementation
        require '../classes/dashboard/Emails.php';
        $email = new Emails();
        $email->setPeriod($period);
        if($period=='custom'){
            $email->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($email->get_emails_data());
        die();
        
        //non-class based implementation
        $where = "";
        
        if(!$is_su){
            $where = " AND client_id = '".$client_unbouce_id."' 
                ORDER BY email_date DESC";
        }
        
        
        $rangeArray = getDateRangeFromPeriod($period);
        $date_from = $rangeArray['date_from'];
        $date_to = $rangeArray['date_to'];
        
        
        $arr_dates = createDateRangeArray($date_from,$date_to);
        $json_array = array();
        foreach($arr_dates as $arr_call){
            $sql = "SELECT count(*) as total FROM emails ";
            $sql .= "WHERE email_date LIKE '%".$arr_call."%' $where";// echo $sql;
            $res = $db->Execute($sql);
            $json_array[] = array('elapsed' => userdate($arr_dates), 'value' => $res->fields['total']);

        }
        //die;
        header('Content-Type: application/json');
        echo json_encode($json_array);
        die();     
        
        
    }
    
    
    if($_GET['act']=='get_emails_table_data'){
        
        if(!isset($_GET['period'])) $period = 'lifetime';
        $period = $_GET['period'];
        
        //class based implementation
        require '../classes/dashboard/Emails.php';
        $email = new Emails();
        $email->setPeriod($period);
        if($period=='custom'){
            $email->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($email->get_email_table_data());
        die();
        
        
        //non-class based implementation
        $where = "";
        
        if(!$is_su){
            $where = " AND client_id = '".$client_unbouce_id."' ";
        }
        
            $rangeArray = getDateRangeFromPeriod($period);
            $date_from = $rangeArray['date_from'];
            $date_to = $rangeArray['date_to'];
        
        $where.=" AND email_date>='$date_from' AND email_date<='$date_to' ";
        
        
        $json_array = array();
        
        $sql = "SELECT * FROM emails WHERE 1 $where ORDER BY email_date DESC ";
        $r = $db->Execute($sql);
        foreach($r as $res){
            $json_array[] = $res;
        }

        
        header('Content-Type: application/json');
        echo json_encode($json_array);
        die();    
    }
    
    
    if($_GET['act']=='get_email_data_weekday'){
        if(!isset($_GET['period'])) $period = 'lifetime';
        else $period = $_GET['period'];
        
        require '../classes/dashboard/Emails.php';
        $emails = new Emails();
        $emails->setPeriod($period);
        if($period=='custom'){
            $emails->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        $data = $emails->get_weekday_stats();
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    // SMS
    ////////////////////////////////////////////////////////////////////////////
    
    if($_GET['act']=='get_sms_data'){
        
        if(!isset($_GET['period'])) $period = 'lifetime';
        $period = $_GET['period'];
        
        
        //class-based implementation
        require '../classes/dashboard/SMS.php';
        $sms = new SMS();
        $sms->setPeriod($period);
        if($period=='custom'){
            $sms->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($sms->get_sms_data());
        die();
        
        //non-class based implementation
        
        $rangeArray = getDateRangeFromPeriod($period);
        $calls_date_from = $rangeArray['date_from'];
        $calls_date_to = $rangeArray['date_to'];
        
        $where = "";
        
        if(!$is_su){
            $where = " AND gsm_number IN( ".$client_id." ) $sms_data_limit_clause
                ORDER BY sms_dt DESC";
        }
        
        
        
        $arr_calls = createDateRangeArray($calls_date_from,$calls_date_to);
        $json_array = array();
        
        foreach($arr_calls as $arr_call){
            $sql = "SELECT count(*) as total FROM sms ";
            $sql .= "WHERE sms_dt LIKE '%".$arr_call."%' $where";// echo $sql;
            $res = $db->Execute($sql);
            $json_array[] = array('elapsed' => userdate($arr_call), 'value' => $res->fields['total']);

        }
        
        header('Content-Type: application/json');
        echo json_encode($json_array);
        die();     
        
        
    }
    
    if($_GET['act']=='get_sms_table_data'){
        
        if(!isset($_GET['period'])) $period = 'lifetime';
        $period = $_GET['period'];
        
        //class based implementation
        require '../classes/dashboard/SMS.php';
        $sms = new SMS();
        $sms->setPeriod($period);
        if($period=='custom'){
            $sms->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        
        header('Content-Type: application/json');
        echo json_encode($sms->get_sms_table_data());
        die();
        
        
        //non-class based implementation
        $where = " WHERE 1 ";
        
        if(!$is_su){
            $where .= " AND gsm_number IN( ".$client_id." ) 
                $sms_data_limit_clause ";
        }
        
        
            $rangeArray = getDateRangeFromPeriod($period);
            $calls_date_from = $rangeArray['date_from'];
            $calls_date_to = $rangeArray['date_to'];
        
        $where.=" AND sms_dt>='$calls_date_from' AND sms_dt<='$calls_date_to' ";
        
        
        $json_array = array();
        
        $sql = "SELECT * FROM sms $where ORDER BY sms_dt DESC ";
        $r = $db->Execute($sql);
        foreach($r as $res){
            $json_array[] = $res;
        }

        
        header('Content-Type: application/json');
        echo json_encode($json_array);
        die();    
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // ROI
    ////////////////////////////////////////////////////////////////////////////
    if($_GET['act']=='get_roi_data'){
        
        if(!isset($_GET['period'])) $period = 'lifetime';
        $period = $_GET['period'];
        
        $rangeArray = getDateRangeFromPeriod($period);
        $date_from = $rangeArray['date_from'];
        $date_to = $rangeArray['date_to'];
        
        header('Content-Type: application/json');
        echo json_encode(getROIData($date_from, $date_to));
        die();
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // Google Analytics
    ////////////////////////////////////////////////////////////////////////////
    
    if($_GET['act']=='get_ga_data'){
        if(!isset($_GET['period'])) $period = 'lifetime';
        $period = $_GET['period'];
        
        require_once dirname(__FILE__).'/../classes/dashboard/DashboardGoogleAnalytics.php';
        $db_ga = new DashboardGoogleAnalytics();
        $db_ga->setPeriod($period);
        if($period=='custom'){
            $db_ga->setCustomPeriod($_REQUEST['from'], $_REQUEST['to']);
        }
        $db_ga->update_analytics();
        $page_view = $db_ga->get_page_views();
        $unique_visitors = $db_ga->get_unique_visitors();
        $chart_analytics = $db_ga->get_chart_data();
        //print_r($chart_analytics);
        $data =  array('page_views'=>$page_view,'unique_visitors'=>$unique_visitors,'chart_data'=>$chart_analytics);
        header('Content-Type: application/json');
        echo json_encode($data);
        die();
    }
    
    
    ////////////////////////////////////////////////////////////////////////////
    // SAVE DATE RANGE
    ////////////////////////////////////////////////////////////////////////////
    
    if($_GET['act']=='save_date_range'){
        if(isset($_GET['period'])){
            
            $period = $_GET['period'];
            $from = $_GET['from'];
            $to = $_GET['to'];
            require '../classes/dashboard/DashboardCommon.php';
           
            DashboardCommon::saveDateRangeFilter($period, $to, $from);
            echo "1";die();
        }
        echo "0";die();
    }
    
    if($_GET['act']=='get_saved_date_range'){
        require '../classes/dashboard/DashboardCommon.php';
        $date_range = DashboardCommon::getSavedDateRangeFilter();
        
        header('Content-Type: application/json');
        echo json_encode($date_range);
        die();
    }
    
    if($_GET['act']=='get_campaign_start_date'){
        require '../classes/dashboard/DashboardCommon.php';
        $start_date = DashboardCommon::getFirstCampaignStartDate();
        
        echo json_encode($start_date);
        die();
    }
}
?>
