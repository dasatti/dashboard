<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('SU'))
    die();

include_once 'include/common.php';

$msg = "";$error="";

if(isset($_REQUEST['sub']) && $_REQUEST['sub']=='delete'){
    if(isset($_REQUEST['id'])){
        $id = (int)$_REQUEST['id'];
        
        
        $q = "DELETE from phone_numbers WHERE id=$id";
        $r = $db->Execute($q);
        if($r){
            $msg = 'Phone number deleted successfully';
        } else{
            $error = 'Error deleting phone number';
        }
    }
}


$q = "SELECT * FROM phone_numbers";
$cl = $db->Execute($q);
$c = $cl->RecordCount();

?>
<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li class="active"> <strong>Phones</strong> </li>
    </ol>
  </div><div class="fr custom-btn"> <a href="admin.php?act=addphone" class="btn btn-blue fr">Add Phone</a> </div>
    <div class="clearfix"></div>
</div>
<h2>Manage Phone Numbers</h2>
<br />
<?php

print_success($msg);
print_error($error);
?>
<div id="sortResult">
    <table class="table table-bordered datatable" id="table-phones">
        <thead>
            <tr>
                <th>Phone Number</th>
                <th>Test</th>
                <th width="20%">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if($c > 0){
                while(!$cl->EOF){
                    
                    ?>
                        <tr>
                        <td><?php echo $cl->fields['phone_number'];?> </td>
                        <td><?php echo ($cl->fields['test_number']==1)?'Yes':'No';?> </td>
                        <td>
                            <a href="admin.php?act=editphone&id=<?php echo $cl->fields['id']; ?>"  class="btn btn-default btn-sm btn-icon icon-left">
                                <i class="entypo-pencil"></i>Edit</a>
                            <a href="admin.php?act=managephones&sub=delete&id=<?php echo $cl->fields['id']; ?>" class="btn btn-danger btn-sm btn-icon icon-left" onClick="return confirm('Are you sure you want to delete this phone number?');">
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
    








<script type="text/javascript">
    
    jQuery(document).ready(function($)
    {
        var table = $("#table-phones").dataTable({
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "oTableTools": {
            }
        });
    });
    
    
</script>