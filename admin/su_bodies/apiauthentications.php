<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if (!defined('SU'))
    die();

include_once 'include/common.php';

if(isset($_REQUEST['sub']) && $_REQUEST['sub']=='delete'){
    if(isset($_REQUEST['id'])){
        $id = (int)$_REQUEST['id'];
        
        
        $q = "DELETE from api_auths WHERE id=$id";
        $r = $db->Execute($q);
        if($r){
            $msg = 'Key deleted successfully';
        } else{
            $error = 'Error deleting key';
        }
    }
}

$q = "SELECT * FROM api_auths";
$cl = $db->Execute($q);
$c = $cl->RecordCount();


?>

<script>

$(document).ready(function(){
    $('#table-apis').dataTable({
        "sPaginationType": "bootstrap",
        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
        "oTableTools": {
        }
    });
});

</script>

<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li class="active"> <strong>API Authentications</strong> </li>
    </ol>
  </div>
    
</div>
<h2>Manage Authentication Keys</h2>
<div class="fr custom-btn"> <a href="admin.php?act=addauthkey" class="btn btn-blue fr">Add Authentication Key</a> </div>
    <div class="clearfix"></div>
<br />

<?php

print_success($msg);
print_error($error);
?>
<div class="row">
    <div class="col-sm-12">
        <div id="sortResult">
            <table class="table table-bordered datatable" id="table-apis">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>GSM Number</th>
                        <th>Authentication Key</th>
                        <th width="20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($c > 0){
                        while(!$cl->EOF){

                            ?>
                                <tr>
                                <td><?php echo $cl->fields['company_name'];?> </td>
                                <td><?php echo $cl->fields['gsm_number'];?> </td>
                                <td><?php echo $cl->fields['auth_key'];?> </td>
                                <td>
                                    <a href="admin.php?act=editauthkey&id=<?php echo $cl->fields['id']; ?>"  class="btn btn-default btn-sm btn-icon icon-left">
                                        <i class="entypo-pencil"></i>Edit</a>
                                    <a href="admin.php?act=apiauthentications&sub=delete&id=<?php echo $cl->fields['id']; ?>" class="btn btn-danger btn-sm btn-icon icon-left" onClick="return confirm('Are you sure you want to delete this key?');">
                                        <i class="entypo-cancel"></i>Delete</a>
                                </td>
                                </tr>
                            <?php 
                            $cl->MoveNext();
                        }
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>