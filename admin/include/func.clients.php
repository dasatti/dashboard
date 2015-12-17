<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */




function getUnassignedPhoneNumbers(){
    
    global $db;
    $sql1 = "SELECT tbl_admin.client_id FROM tbl_admin
            WHERE campaign_end='0000-00-00' OR campaign_end>= CURDATE() ";
    $gsm_ids = array();
    $un_assigned_phones = array();
    $res_gsm = $db->Execute($sql1);
    
    while(!$res_gsm->EOF){
        $cid = $res_gsm->fields['client_id'];
        $cid_arr = explode("#",$cid);

        
        if(!empty($cid_arr)){
            foreach($cid_arr as $c){
                if(trim($cid)!='' && is_numeric(trim($cid)))
                    $gsm_ids[] = $c;	
            }
        }
        else {
            if(trim($cid)!='' && is_numeric(trim($cid)))
                $gsm_ids[] = $cid;
        }
        
        $res_gsm->MoveNext();
    }
    $gsm_ids_q = implode(",", $gsm_ids);
    
    $sql2 = "SELECT phone_number FROM phone_numbers WHERE phone_number NOT IN ($gsm_ids_q)";

    
    $r2 = $db->Execute($sql2);
    while(!$r2->EOF){
        $un_assigned_phones[] = $r2->fields['phone_number'];
        
        $r2->MoveNext();
    }
    
    //print_r($un_assigned_phones);
    
    return $un_assigned_phones;
    
}

function getUnassignedPhoneNumbersEdit($currentNumber){
    $free_numbers = getUnassignedPhoneNumbers();
    $current_numbers = explode('#', $currentNumber);
    if(!empty($current_numbers)){
        foreach($current_numbers as $cn){
            if(!in_array($cn, $free_numbers) && trim($currentNumber)!=''){
                array_push($free_numbers, $cn);
            }
        }
    }else {
        if(!in_array($currentNumber, $free_numbers) && trim($currentNumber)!=''){
            array_push($free_numbers, $currentNumber);
        }
    }
    return $free_numbers;
}
?>
