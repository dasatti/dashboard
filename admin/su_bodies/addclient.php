<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('SU'))
    die();

include_once 'include/common.php';
include_once 'include/func.clients.php';


$msg = "";$error=array();


if(isset($_POST['submit_add_client'])){
    $client_ids = join("#", $_POST['client_id']);
    $_POST = array_map('trim', $_POST);
    //print_r($_POST);die();
    $_POST = array_map('strip_tags', $_POST);
    extract($_POST,EXTR_PREFIX_ALL,'ac');
    $valid = true;
    if(  empty($ac_full_name) || empty($ac_username) || empty($ac_password)  ||  empty($ac_email) || empty($ac_account_type)   ){
        $valid = false;
        $error[] = 'Please provide all the mandatory fields ';
    }
    if(!filter_var($ac_email, FILTER_VALIDATE_EMAIL)){
        $valid = false;
        $error[] = 'Email address is invalid';
    }
    if(!empty($ac_noreply_email) && !filter_var($ac_noreply_email, FILTER_VALIDATE_EMAIL)){
        $valid = false;
        $error[] = 'Noreply email address is invalid';
    }
    if(!empty($ac_notify_email) && !filter_var($ac_notify_email, FILTER_VALIDATE_EMAIL)){
        $valid = false;
        $error[] = 'Notify email address is invalid';
    }
    if($ac_account_type!='super'){
        if($client_ids==''){
            $valid = false;
            $error[] = 'GSM Number (client id) is required for clients';
        }
    }
    
    
    
    if($valid){
        
        /*
         * do not upload, use cropped image url
        //if(uploadFile($_FILES['thumbnail'],$file_e,$upload_path, $allowed)){
        $upload_err = false;
        $file_e="";
        $upload_path = './assets/images/';
        $uploaded_file = 'profile-default.png';
        $allowed = array('jpg','jpeg','png','gif');
        
        if(!empty($_FILES['thumbnail']['name'])){
            
            if(upload_file($file_e, $_FILES['thumbnail'], $upload_path,$allowed)){
                $uploaded_file = $upload_path;
            } else {
                $upload_err = true;
            }
        }
         * */
        $upload_err = false;
        if(!$upload_err){
            $uploaded_file = $ac_cropped_thumbnail;
            $sql = "INSERT INTO tbl_admin(client_id,ga_view_id,name,username,password,email,noreplyemail,notifyemail,account_type,campaign_start,campaign_end,image) VALUES('$client_ids','$ac_ga_view_id','$ac_full_name','$ac_username','$ac_password','$ac_email','$ac_noreply_email','$ac_notify_email','$ac_account_type','$ac_campaign_start','$ac_campaign_end','$uploaded_file')";
            $r = $db->Execute($sql);
            if($r){
                $msg[] = "Client added successfully";
            } else {
                $error[] = "Error adding client";
            }
        } else {
            $error[] = $file_e;
        }
    
    }
}

?>

<!-- Upload & Crop -->
<!--Jquery is required if not already present-->
<!--<script src="assets/jquery-1.11.0.min.js"></script>-->
<!--Bootstrap is required if not already loaded-->
<!-- Latest compiled and minified CSS -->
<!--<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">-->
<!-- Latest compiled and minified JavaScript -->
<!--<script src="assets/bootstrap/js/bootstrap.min.js"></script>-->


<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li> <a href="admin.php?act=manageclients"><i></i>Clients</a> </li>
      <li class="active"> <strong>Add Client</strong> </li>
    </ol>
  
    <div class="clearfix"></div>
    </div>
</div>
<h2>Add Client</h2>
<br />
<?php
print_success($msg);
print_error($error);
?>

<form  role="form" class="addClassForm col-md-12" method="post" action="admin.php?act=addclient" enctype="multipart/form-data">
    
<div class="col-md-8 col-sm-12">
<div class="form-group">
<label class="">GSM Number: </label>
<!--<input type="text" name="client_id" value="<?php echo $_POST['client_id']; ?>" class="form-control">--> 
<select name="client_id[]" class="form-control" multiple="multiple">
<?php 

    $nos = getUnassignedPhoneNumbers();
    foreach ($nos as $no){
        $selected = ($_POST['client_id']==$no)?"selected":"";
        echo '<option value="'.$no.'" '.$selected.'>'.$no.'</option>';
    }

?>
</select>
</div>
<div class="form-group">
<label>Google Analytics View ID:  </label>
<input type="text" name="ga_view_id" value="<?php echo $_POST['ga_view_id']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Full name: * </label>
<input type="text" name="full_name" value="<?php echo $_POST['full_name']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Username: * </label>
<input type="text" name="username" value="<?php echo $_POST['username']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Password: * </label>
<input type="text" name="password" value="<?php echo $_POST['password']; ?>" class="form-control"> 
</div>

<div class="form-group">
<label>Email: * </label>
<input type="text" name="email" value="<?php echo $_POST['email']; ?>" class="form-control"> 
</div>

<div class="form-group">
<label>no-reply Email: </label>
<input type="text" name="noreply_email" value="<?php echo $_POST['noreply_email']; ?>" class="form-control"> 
</div>


<div class="form-group">
<label>Notify Email: </label>
<input type="text" name="notify_email" value="<?php echo $_POST['notify_email']; ?>" class="form-control"> 
</div>

<div class="form-group">
<label>Account Type: * </label>
<select name="account_type" class="form-control">
    <option value="client">Client</option>
    <option value="super">Super Admin</option>
</select>
</div>
<div class="form-group">
    <label>Campaign Start Date: </label>
    <div class="input-group date">
        <input type="text" name="campaign_start" value="<?php echo date('Y-m-d') ?>" id="campaign_start_date" class="form-control"> 
        <span class="input-group-addon">
            <span class="fa fa-calendar">
            </span>
        </span>
    </div>
</div>
<div class="form-group">
    <label>Campaign End Date: </label>
        
    <div class="input-group date">
        <input type="text" name="campaign_end" id="campaign_end_date" class="form-control"> 
        <span class="input-group-addon">
            <span class="fa fa-calendar">
            </span>
        </span>
    </div>
</div>
    
<div class="form-group">
<input class="btn btn-default" type="submit" value="Add Client" name="submit_add_client">
</div>
            
       </div>
        
        <div class="col-md-4 col-sm-12">
            
            <div class="form-group">
<label>Thumbnail: </label>
<input type="file" name="thumbnail" id="thumbnail" onChange="uploadAndCrop();" accept="image/*"> 
<input type="hidden" name="change_thumb" id="change_thumb" value="0">
<input type="hidden" name="cropped_thumbnail" id="cropped_thumbnail" value="<?php echo $_POST['cropped_thumbnail']; ?>">
</div>
<?php
    $tn = "assets/images/thumbs/profile-default.png";
    if(isset($_POST['cropped_thumbnail']) && trim($_POST['cropped_thumbnail'])!=''){
        $tn = trim($_POST['cropped_thumbnail']);
    }
?>
<div id="thumbnail_img">
    <div id="upload_error"></div>
    <img src="<?php echo $tn; ?>" width="200" height="200" id="final_thumbnail">
</div>
        </div>
    
    

</form>
<div class="clearfix"></div>
<script>

$(document).ready(function(){
    $('#campaign_start_date').datepicker({
        format:'yyyy-mm-dd'
    });
    $('#campaign_end_date').datepicker({
        format:'yyyy-mm-dd'
    });
    
    $("#campaign_start_date").on("dp.change", function (e) {
        $('#campaign_end_date').data("DateTimePicker").minDate(e.date);
    });
    $("#campaign_end_date").on("dp.change", function (e) {
        $('#campaign_start_date').data("DateTimePicker").maxDate(e.date);
    });
})

</script>
        
<!-- Upload and Crop Modal-->
<?php
    include "thumbnail_upload_crop_modal.php";

?>