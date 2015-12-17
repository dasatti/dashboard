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
        //fetch the existing thumbnail
        $sql = "SELECT image from tbl_admin WHERE id='$id'";
        $r1 = $db->Execute($sql);
        if($r1->RecordCount()>0){
            $old_thumb = $r1->fields['image'];
            $upload_path = './assets/images/';
            if(!$old_thumb!='' && $old_thumb!='profile-default.png')
                unlink($upload_path.$old_thumb);
        }
        
        $q = "DELETE from tbl_admin WHERE id=$id";
        $r = $db->Execute($q);
        if($r){
            $msg = 'Client Deleted Successfully';
        } else{
            $error = 'Error deleting client';
        }
    }
}


$q = "SELECT * FROM tbl_admin";
$cl = $db->Execute($q);
$c = $cl->RecordCount();

?>
<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li class="active"> <strong>Clients</strong> </li>
    </ol>
  </div><div class="fr custom-btn"> <a href="admin.php?act=addclient" class="btn btn-blue fr">Add Client</a> </div>
    <div class="clearfix"></div>
</div>
<h2>Manage Clients</h2>
<br />
<?php
print_success($msg);
print_error($error);
?>
<div id="sortResult">
    <table class="table table-bordered datatable" id="table-clients">
        <thead>
            <tr>
                <!--<th>S.No</th>-->
                <th>Name</th>
                <th>GSM</th>
                <th>Campaign</th>
                <th>Email</th>
                <th>Username</th>
                <th>Start</th>
                <th>End</th>
                <th>Type</th>
                <!--<th>Campaign End</th>-->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if($c > 0){
                while(!$cl->EOF){
                    $campaign_status = "Running"; $campaign_class="success";
                    if($cl->fields['campaign_end']!='0000-00-00' && $cl->fields['campaign_end'] < date("Y-m-d")){
                        $campaign_status="Stopped";
                        $campaign_class = "danger";
                    }
                    ?>
                        <tr>
                        <!--<td></td>-->
                        <td><?php echo $cl->fields['name'];?> </td>
                        <td><?php echo $cl->fields['client_id'];?> </td>
                        <td><div class="label label-<?php echo $campaign_class; ?>"><?php echo $campaign_status; ?></div></td>
                        <td><?php echo $cl->fields['email'];?> </td>
                        <td><?php echo $cl->fields['username'];?> </td>
                        <td><?php echo $cl->fields['campaign_start'];?> </td>
                        <td><?php echo $cl->fields['campaign_end'];?> </td>
                        <td><?php echo $cl->fields['account_type'];?> </td>
                        
                        <!--<td><?php //echo $cl->fields['campaign_end'];?> </td>-->
                        <td align="center"><a href="admin.php?act=editclient&id=<?php echo $cl->fields['id']; ?>"
                               class="btn btn-default btn-sm btn-icon icon-left">
                                <i class="entypo-pencil"></i>Edit</a>
                            <a href="admin.php?act=manageclients&sub=delete&id=<?php echo $cl->fields['id']; ?>"
                               class="btn btn-danger btn-sm btn-icon icon-left">
                                <i class="entypo-cancel" onClick="return confirm('Are you sure you want to delete this client?');"></i>Del</a> </td>
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
        var table = $("#table-clients").dataTable({
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "aoColumns": [
//              { "bSortable": false },
              null,
              null,
                null,
                null,
                null,
                null,
                null,
                null,
              { "bSortable": false }
              ]
        });
	   $(".dataTables_wrapper select").select2({
		minimumResultsForSearch: -1
	});
    });
    
    
</script>