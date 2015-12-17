<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('SU'))
    die();

include_once 'include/common.php';
include_once 'include/func.clients.php';

if(!isset($_GET['id'])) js_redirect ('admin.php?act=managephones');

$msg = "";$error=array();

if(isset($_POST['submit_edit_phone'])){
    $_POST = array_map('trim', $_POST);
    $_POST = array_map('strip_tags', $_POST);
    extract($_POST,EXTR_PREFIX_ALL,'ap');
    $valid = true;
    
    if(  $ap_phone_number=='' ){
        $valid = false;
        $error[] = 'Please provide phone number ';
    }    
    if(  !is_numeric($ap_phone_number) ){
        $valid = false;
        $error[] = 'Phone number must be a valid number ';
    } 
    
    
    
    if($valid){
        $sql = "UPDATE phone_numbers SET phone_number='$ap_phone_number',
            test_number='$ap_test_number' WHERE id=$ap_p_id";
        $r = $db->Execute($sql);
        if($r){
            $msg[] = "Phone number updated successfully";
        } else {
            $error[] = "Error updating phone number";
        }
        
    
    }
}

$phone_id = (int)$_GET['id'];
$sql = "SELECT * FROM phone_numbers WHERE id=$phone_id";
$res = $db->Execute($sql);
if($res->RecordCount()<=0){
    js_redirect('admin.php?act=managephones');
}

?>
<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li> <a href="admin.php?act=managephones"><i></i>Phone Numbers</a> </li>
      <li class="active"> <strong>Update Phone</strong> </li>
    </ol>
  
    <div class="clearfix"></div>
    </div>
</div>
<h2>Update Phone Number</h2>
<br />
<?php
print_success($msg);
print_error($error);

?>

<form method="post" class="addClassForm col-sm-8" action="admin.php?act=editphone&id=<?php echo $phone_id; ?>">
    <input type="hidden" name="p_id" value="<?php echo $res->fields['id']; ?>" />

<div class="form-group">
<label>Phone Number: </label><input type="text" name="phone_number" value="<?php echo $res->fields['phone_number']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label class="">Test Number: </label>
<select class="form-control" name="test_number">
    <option value="0" <?php echo ($res->fields['test_number']=='0')?"selected":""; ?>>No</option>
    <option value="1" <?php echo ($res->fields['test_number']=='1')?"selected":""; ?>>Yes</option>
</select>
</div>

    
<div class="form-group">
<input type="submit" value="Update Phone"e name="submit_edit_phone">
</div>

</form>
<div class="clearfix"></div>






