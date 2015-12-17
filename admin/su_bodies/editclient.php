<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('SU'))
    die();

include_once 'include/common.php';
include_once 'include/func.clients.php';

if(!isset($_GET['id'])) js_redirect ('admin.php?act=manageclients');

$msg = "";$error=array();

if(isset($_POST['submit_edit_client'])){
    
    $client_ids = join("#", $_POST['client_id']);
    $_POST = array_map('trim', $_POST);
    $_POST = array_map('strip_tags', $_POST);
    extract($_POST,EXTR_PREFIX_ALL,'ac');
    $valid = true;
    
    if( empty($ac_full_name) || empty($ac_username) || empty($ac_password)  ||  empty($ac_email) || empty($ac_account_type)   ){
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
    
    
    
    if($valid){
        $upload_err = false;
        $file_e="";
        $upload_path = './';//'./assets/images/';//new uploaded path
        $old_thumb = '';
        $uploaded_file = 'assets/images/profile-default.png';
        $allowed = array('jpg','jpeg','png','gif');
        
        //fetch the existing thumbnail
        $sql = "SELECT image from tbl_admin WHERE id='$ac_c_id'";
        $r1 = $db->Execute($sql);
        if($r1->RecordCount()>0){
            $old_thumb = $r1->fields['image'];
            $uploaded_file = $old_thumb;
        }
        
        /*
        if(!empty($_FILES['thumbnail']['name'])){
            if($old_thumb!='assets/images/thumbs/profile-default.png')
//            if(preg_match('/profile-default.png$/', $old_thumb))
                unlink($upload_path.$old_thumb);
            
            if(upload_file($file_e, $_FILES['thumbnail'], $upload_path,$allowed)){
                $uploaded_file = $upload_path;
            } else {
                $upload_err = true;
            }
        } else {
            $uploaded_file = $old_thumb;
        }
        */
        
        //delete older thumbnail
        if($ac_change_thumb==1 && trim($ac_cropped_thumbnail)!=''){
            
            if(!preg_match('/profile-default.png$/', $old_thumb)){
                    unlink($upload_path.$old_thumb);
            }
            
            $uploaded_file = $ac_cropped_thumbnail;
        }
        
        
        if(!$upload_err){
            
            $sql = "UPDATE tbl_admin SET client_id = '$client_ids',ga_view_id='$ac_ga_view_id',name='$ac_full_name',username='$ac_username',password='$ac_password',email='$ac_email',noreplyemail='$ac_noreply_email',notifyemail='$ac_notify_email',account_type='$ac_account_type',image='$uploaded_file',
                campaign_start='$ac_campaign_start', campaign_end='$ac_campaign_end',
                avg_value_of_sale='$ac_avg_value_of_sale',avg_lead_to_sale='$ac_avg_lead_to_sale'
                WHERE id='$ac_c_id'";
            //echo 'here-->',$sql;die;
            $r = $db->Execute($sql);
            if($r){
                $msg[] = "Client updated successfully";
            } else {
                $error[] = "Error adding client";
                $error[] = $sql;
            }
        } else {
            $error[] = $file_e;
        }
    
    }
}

$client_id = (int)$_GET['id'];
$sql = "SELECT * FROM tbl_admin WHERE id=$client_id";
$res = $db->Execute($sql);
if($res->RecordCount()<=0){
    js_redirect('admin.php?act=manageclients');
}

?>
<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li> <a href="admin.php?act=manageclients"><i></i>Clients</a> </li>
      <li class="active"> <strong>Update Client</strong> </li>
    </ol>
  
    <div class="clearfix"></div>
    </div>
</div>
<h2>Update Client</h2>
<br />
<?php
print_success($msg);
print_error($error);

$profile_image = (!empty($res->fields['image']))?$res->fields['image']:'./assets/images/thumbs/profile-default.png';
?>

<form method="post" class="addClassForm col-md-12" action="admin.php?act=editclient&id=<?php echo $client_id; ?>" enctype="multipart/form-data">
    <input type="hidden" name="c_id" value="<?php echo $res->fields['id']; ?>" />

<div class="col-md-8 col-sm-12">
<div class="form-group">
<label>GSM Number (Client ID): </label>
<!--<input type="text" name="client_id" value="<?php echo $res->fields['client_id']; ?>" class="form-control">--> 
<select name="client_id[]" class="form-control" multiple="multiple">
<?php 

    $nos = getUnassignedPhoneNumbersEdit($res->fields['client_id']);
    foreach ($nos as $no){
        $selected = ($res->fields['client_id']==$no)?"selected":"";
        echo '<option value="'.$no.'" '.$selected.'>'.$no.'</option>';
    }

?>
</select>
</div>
<div class="form-group">
<label>Goggle Analytics View ID:</label><input type="text" name="ga_view_id" value="<?php echo $res->fields['ga_view_id']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Full name: * </label><input type="text" name="full_name" value="<?php echo $res->fields['name']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Username: * </label><input type="text" name="username" value="<?php echo $res->fields['username']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Password: * </label><input type="text" name="password" value="<?php echo $res->fields['password']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Email: * </label><input type="text" name="email" value="<?php echo $res->fields['email']; ?>" class="form-control"> </br>
<label>no-reply Email: </label><input type="text" name="noreply_email" value="<?php echo $res->fields['noreplyemail']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Notify Email: </label><input type="text" name="notify_email" value="<?php echo $res->fields['notifyemail']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Account Type: * </label>
<select name="account_type" class="form-control">
    <option value="client">Client</option>
    <option value="super" <?php echo ($res->fields['account_type']=='super')?'selected':''; ?>>Super Admin</option>
</select>

</div>
<div class="form-group">
    <label>Campaign Start Date: </label>
    <div class="input-group date">
        <input type="text" name="campaign_start" value="<?php echo $res->fields['campaign_start']; ?>" id="campaign_start_date" class="form-control"> 
        <span class="input-group-addon">
            <span class="fa fa-calendar">
            </span>
        </span>
    </div>
</div>
<div class="form-group">
    <label>Campaign End Date: </label>
        
    <div class="input-group date">
        <input type="text" name="campaign_end" value="<?php echo $res->fields['campaign_end']; ?>" id="campaign_end_date" class="form-control"> 
        <span class="input-group-addon">
            <span class="fa fa-calendar">
            </span>
        </span>
    </div>
</div>
    
    
<div class="form-group">
<label>Average value of sale: </label><input type="text" name="avg_value_of_sale" value="<?php echo $res->fields['avg_value_of_sale']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label>Average Lead to Sale conversion percentage: </label><input type="text" name="avg_lead_to_sale" value="<?php echo $res->fields['avg_lead_to_sale']; ?>" class="form-control"> 
</div>
<div class="form-group">
    <input type="submit" value="Update Client" name="submit_edit_client">
</div>

</div>
    <div class="col-md-4 col-sm-12">
        <div class="form-group">
        <label>Thumbnail: </label><input type="file" name="thumbnail" id="thumbnail"  onChange="uploadAndCrop();" accept="image/*"> 
        </div>
        <div class="form-group">
            <div id="upload_error"></div>
            <img src="./<?php echo $profile_image; ?>"  style="max-width: 200px; max-width: 200px;" id="final_thumbnail">
            <input type="hidden" name="change_thumb" id="change_thumb" value="0">
            <input type="hidden" name="cropped_thumbnail" id="cropped_thumbnail" value="<?php echo $_POST['cropped_thumbnail']; ?>">
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
