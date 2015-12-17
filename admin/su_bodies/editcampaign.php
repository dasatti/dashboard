<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('SU'))
    die();

include_once 'include/common.php';
include_once 'include/func.clients.php';

ini_set("display_errors", 1);
error_reporting(E_ERROR);


if(!isset($_GET['id'])) js_redirect ('admin.php?act=manageclients');

require_once realpath(dirname(__FILE__)). '/../classes/dashboard/Campaign.php';
$campaign = new Campaign();

$msg = "";$error=array();


if(isset($_POST['submit_edit_campaign'])){
    
    $_POST = array_map('trim', $_POST);
    $_POST = array_map('strip_tags', $_POST);
    
    extract($_POST);
    
    if($campaign->edit($campaign_id,$campaign_name, $gsm_number, $unbounce_id, $client_id, $ga_view_id,$start_date,$end_date)){
        $msg = "campaign edited successfully";
    } else {
        $error = $campaign->get_error();
//        $error[] = 'could not update campaign';
    }
}


$c = $campaign->get_campaign((int)$_GET['id']);
$clients = Campaign::get_clients_list();
$gsm_numebrs = Campaign::get_gsm_numbers();

?>



<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
      <li> <a href="admin.php?act=managecampaigns"><i></i>campaigns</a> </li>
      <li class="active"> <strong>Update Campaign</strong> </li>
    </ol>
  
    <div class="clearfix"></div>
    </div>
</div>
<h2>Update Campaign</h2>
<br />
<?php
print_success($msg);
print_error($error);
?>

<form  role="form" class="addClassForm col-md-12" method="post" action="admin.php?act=editcampaign&id=<?php echo $c['id']; ?>" >
    
    <input type="hidden" name="campaign_id" value="<?php echo $c['id']; ?>">
<div class="col-md-8 col-sm-12">
<div class="form-group">
    <label class="">Campaign name: *</label>
    <input type="text" name="campaign_name" value="<?php echo $c['campaign_name']; ?>" class="form-control"> 
</div>
<div class="form-group">
    <label>Client:  </label>
    <select name="client_id" class="form-control"> 
        <option value=""></option>
        <?php
        foreach($clients as $cl):
            $sel = ($c['client_id']==$cl['id'])?'selected':'';
            ?><option value="<?php echo $cl['id']; ?>" <?php echo $sel; ?>><?php echo $cl['name']; ?></option><?php
        endforeach;
        ?>
    </select>
</div>
     
<div class="form-group">
    <label>GSM Number: *</label>
    <select name="gsm_number" class="form-control"> 
        <option value=""></option>
        <?php
        foreach($gsm_numebrs as $gsm):
            $sel = ($c['gsm_number']==$gsm['phone_number'])?'selected':'';
            ?><option value="<?php echo $gsm['phone_number']; ?>"  <?php echo $sel; ?>><?php
                echo $gsm['phone_number'];
            ?></option><?php
        endforeach;
        ?>
    </select>
</div>
<div class="form-group">
    <label>Unbounce ID: *</label>
    <input type="text" name="unbounce_id" value="<?php echo $c['unbounce_id']; ?>" class="form-control"> 
</div>
<div class="form-group">
    <label>Google Analytics View ID:  </label>
    <input type="text" name="ga_view_id" value="<?php echo $c['ga_view_id']; ?>" class="form-control"> 
</div>
    
<div class="form-group">
    <label>Campaign Start Date: </label>
    <div class="input-group date">
        <?php $start_date = ($c['start_date']!='0000-00-00')?$c['start_date']:''; ?>
        <input type="text" name="start_date" value="<?php echo $start_date; ?>" id="start_date" class="form-control"> 
        <span class="input-group-addon">
            <span class="fa fa-calendar">
            </span>
        </span>
    </div>
</div>
<div class="form-group">

<label>Campaign End Date: </label>
        
    <div class="input-group date">
        <?php $end_date = ($c['end_date']!='0000-00-00')?$c['end_date']:''; ?>
        <input type="text" name="end_date" value="<?php echo $end_date; ?>" id="end_date" class="form-control"> 
        <span class="input-group-addon">
            <span class="fa fa-calendar">
            </span>
        </span>
    </div>
</div>

    
<div class="form-group">
<input class="btn btn-default" type="submit" value="Update Campaign" name="submit_edit_campaign">
</div>
            
       </div>
        
        
    
    

</form>
<div class="clearfix"></div>

<script>

$(document).ready(function(){
    $('#start_date').datepicker({
        format:'yyyy-mm-dd'
    });
    $('#end_date').datepicker({
        format:'yyyy-mm-dd'
    });
    
    $("#start_date").on("dp.change", function (e) {
        $('#end_date').data("DateTimePicker").minDate(e.date);
    });
    $("#end_date").on("dp.change", function (e) {
        $('#start_date').data("DateTimePicker").maxDate(e.date);
    });
})

</script>