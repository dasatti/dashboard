<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('SU'))
    die();

include_once 'include/common.php';


$msg = "";$error=array();

if(isset($_POST['submit_add_phone'])){
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
        
        $sql = "INSERT INTO phone_numbers(phone_number,test_number) VALUES('$ap_phone_number','$ap_test_number')";
        $r = $db->Execute($sql);
        if($r){
            $msg[] = "Phone number added successfully";
        } else {
            $error[] = "Error adding phone number";
        }
        
    
    }
}

?>
<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li> <a href="admin.php?act=managephones"><i></i>Manage Phones</a> </li>
      <li class="active"> <strong>Add Phone</strong> </li>
    </ol>
  
    <div class="clearfix"></div>
    </div>
</div>
<h2>Add Phone</h2>
<br />
<?php
print_success($msg);
print_error($error);
?>

<form  role="form" class="addClassForm col-sm-8" method="post" action="admin.php?act=addphone">
<div class="form-group">
<label class="">Phone Number: *</label>
<input type="text" name="phone_number" value="<?php echo $_POST['phone_number']; ?>" class="form-control"> 
</div>
<div class="form-group">
<label class="">Test Number: </label>
<select class="form-control" name="test_number">
    <option value="0">No</option>
    <option value="1">Yes</option>
</select>
</div>

<div class="form-group">
<input class="btn btn-default" type="submit" value="Add Phone" name="submit_add_phone">
</div>

</form>
<div class="clearfix"></div>