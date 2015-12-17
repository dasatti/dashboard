<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('SU'))
    die();

ini_set("display_errors", 1);
error_reporting(E_ALL);


include_once 'include/common.php';

$msg = "";$error="";

require_once realpath(dirname(__FILE__)). '/../classes/dashboard/Campaign.php';
$campaign = new Campaign();


if(isset($_REQUEST['sub']) && $_REQUEST['sub']=='delete'){
    if(isset($_REQUEST['id'])){
        $id = (int)$_REQUEST['id'];
        if($campaign->delete($id)){
            $msg = "Campaign deleted successfully";
        } else {
            $error = "Error deleting campaign";
        }
    }
}

$q = "SELECT * FROM campaigns";
$r = $db->Execute($q);

$campaigns = $campaign->get_campaigns();

?>
<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li class="active"> <strong>Campaigns</strong> </li>
    </ol>
  </div><div class="fr custom-btn"> <a href="admin.php?act=addcampaign" class="btn btn-blue fr">Add Campaign</a> </div>
    <div class="clearfix"></div>
</div>
<h2>Manage Campaigns</h2>
<br />
<?php
print_success($msg);
print_error($error);
?>
<div id="sortResult">
    <table class="table table-bordered datatable" id="table-campaigns">
        <thead>
            <tr>
                <th>Campaign</th>
                <th>Client Name</th>
                <th>GSM Number</th>
                <th>Unbounce ID</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
               foreach ($campaigns as $c){
                    $campaign_status = "Running"; $campaign_class="success";
                    if($c['end_date']!='0000-00-00' && $c['end_date'] < date("Y-m-d")){
                        $campaign_status="Stopped";
                        $campaign_class = "danger";
                    }
                    ?>
                        <tr>
                        <!--<td></td>-->
                        <td><?php echo $c['campaign_name']; ?> </td>
                        <td><?php echo $c['name'];  ?> </td>
                        <td><?php echo $c['gsm_number']; ?> </td>
                        <td><?php echo $c['unbounce_id']; ?> </td>
                        <td><?php echo $c['start_date']; ?> </td>
                        <td><?php echo $c['end_date']; ?> </td>
                        <td><div class="label label-<?php echo $campaign_class; ?>"><?php echo $campaign_status; ?></div></td>
                        
                        <td align="center"><a href="admin.php?act=editcampaign&id=<?php echo $c['id']; ?>"
                               class="btn btn-default btn-sm btn-icon icon-left">
                                <i class="entypo-pencil"></i>Edit</a>
                            <a href="admin.php?act=managecampaigns&sub=delete&id=<?php echo $c['id']; ?>"
                               class="btn btn-danger btn-sm btn-icon icon-left"
                                onClick="return confirm('Are you sure you want to delete this campaign?');">
                                <i class="entypo-cancel"></i>Del</a> </td>
                        </tr>
                    <?php
                }
            ?>
            
        </tbody>
    </table>
</div>
    








<script type="text/javascript">
    jQuery(document).ready(function(){
            var table = $("#table-campaigns").dataTable({
                "sPaginationType": "bootstrap",
                "order": [[ 1, "asc" ]],
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                "columns":[,,,,{"visible":false},{"visible":false},,,]
            });
            $('document').dtToggleCols("#table-campaigns");
        }
    );
    
    
</script>