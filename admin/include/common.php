<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 * @author: dasatti
 */



function print_error($msg){
    if(!empty($msg)){
        if(is_array($msg)){
            $msg = implode("</br>", $msg);
        }
        echo "<div class=\"error\">$msg</div>";
    }
}

function print_success($msg){
    if(!empty($msg)){
        if(is_array($msg)){
            $msg = implode("</br>", $msg);
        }
        echo "<div class=\"success\">$msg</div>";
    }
}

function print_info($msg){
    if(!empty($msg)){
        if(is_array($msg)){
            $msg = implode("</br>", $msg);
        }
        echo "<div class=\"info\">$msg</div>";
    }
}

function upload_file(&$msg, $file, &$target_dir,$allowed_array, $max_size=500000){
    if(empty($allowed_array)){
        $allowed_array = array('jpg','jpeg','png','gif','pdf','txt');
    }
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
    // Check if image file is a actual image or fake image
    $msg = "";
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        $msg = "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        $msg = "File is not an image.";
        $uploadOk = 0;
    }
    
    // Check if file already exists
    if (file_exists($target_file)) {
        $msg = "Sorry, file already exists.";
        $uploadOk = 0;
    }
    // Check file size
    if ($file["size"] > $max_size) {
        $msg = "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if(!in_array($imageFileType, $allowed_array) ) {
        $msg = "Sorry, Invalid file type.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        //$msg = "Sorry, your file was not uploaded.";
        return false;
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $target_dir = $file["name"];
            $msg = "The file ". basename( $file["name"]). " has been uploaded.";
            return true;
        } else {
            $msg = "Sorry, there was an error uploading your file.";
            return false;
        }
    }
}

function js_redirect($url){
    if(!empty($url)){
        echo '<script>document.location="'.$url.'"</script>';
    }
}



function getDateRangeFromPeriod($period){
    $dateRange = array();
    
    switch ($period){
        case 'lifetime':
            $from = '2014-09-01';$to = date('Y-m-d',strtotime("+1 days"));
            break;
        case 'month':
            $from = date('Y-m-d',strtotime("-30 days"));$to = date('Y-m-d',strtotime("+1 days"));
            break;
        case 'week':
            $from = date('Y-m-d',strtotime("-7 days"));$to = date('Y-m-d',strtotime("+1 days"));
            break;
        case 'daily':
        case 'today':
            $from = date('Y-m-d');$to = date('Y-m-d',strtotime("+1 days"));
            break;
        case 'yesterday':
            $from = date('Y-m-d',strtotime("-1 days"));$to = date('Y-m-d');
            break;
        case 'last_7_days':
            $from = $calls_date_from = date('Y-m-d',strtotime("-7 days"));
            $to = date('Y-m-d');
            break;
        case 'last_30_days':
            $from = date('Y-m-d',strtotime("-30 days"));$to = date('Y-m-d');
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


// function defination to convert array to xml
function array_to_xml($arr, &$xml) {
    foreach($arr as $key => $value) {
        if(is_array($value)) {
            if(!is_numeric($key)){
                $subnode = $xml->addChild("$key");
                array_to_xml($value, $subnode);
            }
            else{
                $subnode = $xml->addChild("item$key");
                array_to_xml($value, $subnode);
            }
        }
        else {
            $xml->addChild("$key",htmlspecialchars("$value"));
        }
    }
}



?>
