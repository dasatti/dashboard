<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('SU'))
    die();

include_once 'include/common.php';


$msg = "";$error=array();

if(isset($_POST['submit_add_key'])){
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
        $sql = "INSERT INTO api_auths(company_name,gsm_number,auth_key)
                VALUES('$api_company_name','$api_gsm_number','$api_authentication_key')";
        $r = $db->Execute($sql);
        if($r){
            $msg[] = "Key added successfully";
        } else {
            $error[] = "Error adding key";
        }
    }
}


$token = md5(uniqid(rand(), true));

?>
<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li> <a href="admin.php?act=apiauthentications"><i></i>Manage Authentication Keys</a> </li>
      <li class="active"> <strong>API Authentications</strong> </li>
    </ol>
  </div>
    
</div>
<h2>Add Keys</h2>
    <div class="clearfix"></div>
<br />

<?php

print_success($msg);
print_error($error);
?>
<div class="row">
    <div>
        <form  role="form" class="form-horizontal" method="post" action="admin.php?act=addauthkey">
            
            <div class="form-group">
                <label for="company_name" class="col-sm-2 control-label">Company Name : </label>
                <div class="col-sm-4">
                  <input type="text" name ="company_name" class="form-control" id="inp_company_name" placeholder="Company Name" value="<?php echo $api_company_name; ?>">
                </div>
                <div class="col-sm-6"></div>
              </div>
            <div class="form-group">
                <label for="inp_gsm_number" class="col-sm-2 control-label">GSM Number : </label>
                <div class="col-sm-4">
                  <input type="text" name ="gsm_number" class="form-control" id="inp_gsm_number" placeholder="GSM Number" value="<?php echo $api_gsm_number; ?>">
                </div>
                <div class="col-sm-6"></div>
              </div>
              <div class="form-group">
                <label for="inp_authentication_key" class="col-sm-2 control-label">Authentication Key : </label>
                <div class="col-sm-4">
                    <input type="text" name="authentication_key" class="form-control" id="inp_authentication_key" placeholder="Key" value="<?php echo $token; ?>" readonly="readonly">
                </div>
                <div class="col-sm-6"></div>
              </div>
            
            <div class="form-group">
                <div class="col-sm-2"></div>
                <div class="col-sm-4">
                    <input class="btn btn-default" type="submit" value="Add Key" name="submit_add_key">
                </div>
                <div class="col-sm-6"></div>
            
            </div>
            
        </form>
        <div class="clearfix"></div>
    </div>
</div>