<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

error_reporting(E_ALL);
require_once('../../adodb/adodb.inc.php');
require_once('../../include/siteconfig.inc.php');
require_once('../../include/sitefunction.php');
require_once('../include/common.php');


$db = ADONewConnection('mysql');
$db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");



function getCallsData($gsm,$from,$to){
    global $db;
    
    $sql = "SELECT * FROM calls WHERE gsm_number = '$gsm' AND call_start>='$from'
            AND call_end<='$to' AND test_data=0";
    $r = $db->Execute($sql);
    $data = array();
    while(!$r->EOF){
        $data[] = array('callerid'=>$r->fields['callerid'],'gsm_number'=>$r->fields['gsm_number '],
            'forward_number'=>$r->fields['forward_number'],'call_start'=>$r->fields['call_start'],
            'call_end'=>$r->fields['call_end'],'total_duration'=>$r->fields['total_duration'],
            'answer_duration'=>$r->fields['answer_duration'],'status'=>$r->fields['status']
                );
        //$data[] = array('callerid'=>$r->fields['callerid']);
        $r->MoveNext();
    }
    
    return $data;
}

function getGSMFromToken($token){
    global $db;
    
    $sql = "SELECT gsm_number FROM api_auths 
            WHERE auth_key='$token' and status=1 LIMIT 1";
    $r = $db->Execute($sql);
    
    if($r){
        return $r->fields['gsm_number'];
    }
    return '';
}


if(isset($_REQUEST['get']) && isset($_REQUEST['t'])){
    
    $token = $_REQUEST['t'];
    $gsm = getGSMFromToken($token);
    $data = array();
    
    $format = 'xml';
    $allowed_formats = array('xml','json');
    if(isset($_REQUEST['format']) && in_array($_REQUEST['format'], $allowed_formats)){
        $format = $_REQUEST['format'];
    }
    
    if($gsm==''){
        echo json_encode(array('not authenticated'));
        die;
    }
    
    
    
    if($_REQUEST['get']=='calls'){
        
        if(!isset($_GET['period'])) $period = 'lifetime';
        else $period = $_GET['period'];
            
        $rangeArray = getDateRangeFromPeriod($period);
        $from = $rangeArray['date_from'];
        $to = $rangeArray['date_to'];
        
        $data = getCallsData($gsm,$from,$to);
    }
    
    
    header("Content-Type: application/$format");
    
    if($format=='json'){
        echo json_encode($data);
        die();
    }
    else{
        $xml = new SimpleXMLElement('<root/>');
        array_to_xml($data,$xml);
        print $xml->asXML();
        die;
    }
    
    die();
}



?>
