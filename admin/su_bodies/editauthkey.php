<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('SU'))
    die();


include_once 'include/common.php';

if(!isset($_GET['id'])) js_redirect ('admin.php?act=apiauthentications');

$msg = "";$error=array();



if(isset($_POST['submit_edit_key'])){
    $_POST = array_map('trim', $_POST);
    $_POST = array_map('strip_tags', $_POST);
    
    extract($_POST,EXTR_PREFIX_ALL,'api');
    $valid = true;
    if($api_company_name==''){
        $valid=false;
        $error[] = 'Company name can not be empty';
    }
    if($api_gsm_number==''){
        $valid=false;
        $error[] = 'GSM Number can not be empty';
    }
    if($api_authentication_key==''){
        $valid=false;
        $error[] = 'Authntication Number can not be empty';
    }
    
    if($valid){
        $sql = "UPDATE api_auths SET company_name='$api_company_name',gsm_number='$api_gsm_number',
            auth_key='$api_authentication_key' WHERE id=$api_key_id";
        $r = $db->Execute($sql);
        if($r){
            $msg[] = "Key added successfully";
        } else {
            $error[] = "Error adding key";
        }
    }
}



$key_id = (int)$_GET['id'];
$sql = "SELECT * FROM api_auths WHERE id=$key_id";
$res = $db->Execute($sql);
if($res->RecordCount()<=0){
    js_redirect('admin.php?act=apiauthentications');
}

?>
<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li> <a href="admin.php?act=apiauthentications"><i></i>Manage Authentication Keys</a> </li>
      <li class="active"> <strong>Edit Key</strong> </li>
    </ol>
  </div>
    
</div>
<h2>Edit Keys</h2>
    <div class="clearfix"></div>
<br />
<?php
print_success($msg);
print_error($error);

?>
<div class="row">
    <div>
        <form  role="form" class="form-horizontal" method="post" action="admin.php?act=editauthkey&id=<?php echo $key_id; ?>">
            <input type="hidden" name="key_id" value="<?php echo $key_id; ?>">
            <div class="form-group">
                <label for="company_name" class="col-sm-2 control-label">Company Name : </label>
                <div class="col-sm-4">
                  <input type="text" name ="company_name" class="form-control" id="inp_company_name" placeholder="Company Name" value="<?php echo $res->fields['company_name']; ?>">
                </div>
                <div class="col-sm-6"></div>
              </div>
            <div class="form-group">
                <label for="inp_gsm_number" class="col-sm-2 control-label">GSM Number : </label>
                <div class="col-sm-4">
                  <input type="text" name ="gsm_number" class="form-control" id="inp_gsm_number" placeholder="GSM Number" value="<?php echo $res->fields['gsm_number']; ?>">
                </div>
                <div class="col-sm-6"></div>
              </div>
              <div class="form-group">
                <label for="inp_authentication_key" class="col-sm-2 control-label">Authentication Key : </label>
                <div class="col-sm-4">
                    <input type="text" name="authentication_key" class="form-control" id="inp_authentication_key" placeholder="Key" value="<?php echo $res->fields['auth_key']; ?>" readonly="readonly">
                </div>
                <div class="col-sm-6"></div>
              </div>
            
            <div class="form-group">
                <div class="col-sm-2"></div>
                <div class="col-sm-4">
                    <input class="btn btn-default" type="submit" value="Edit Key" name="submit_edit_key">
                </div>
                <div class="col-sm-6"></div>
            
            </div>
            
        </form>
        <div class="clearfix"></div>
    </div>
</div>