<?php 
include('../../adodb/adodb.inc.php');
include('../../include/siteconfig.inc.php');
include('../../include/sitefunction.php');
include('../script_include.php');
$db = ADONewConnection('mysql');
$db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");


//restrict data fetching within curent campaign period
$start_date_limit = $_SESSION['lm_auth']['campaign_start'];
$end_date_limit = $_SESSION['lm_auth']['campaign_end'];
$calls_data_limit_clause = "";
$email_data_limit_clause = "";
if($start_date_limit!="" && $start_date_limit!="0000-00-00"){
    $calls_data_limit_clause = " AND call_start>='$start_date_limit'";
    $email_data_limit_clause = " AND email_date>='$start_date_limit'";
}
if($end_date_limit!="" && $end_date_limit!="0000-00-00"){
    $calls_data_limit_clause = " AND call_end<='$end_date_limit'";
    $email_data_limit_clause = " AND email_date<='$end_date_limit'";
}

if(isset($_GET['act'])){
	
	
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//****************************************************************** Calls Section ****************************************************//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
        $is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
	
	//################################//
	//######## Calls Listing ########//
	//##############################//
	//error_reporting(E_ALL);
	if($_GET['act']=='doSelectedSort'){
	
            $sortVal = $_GET['sortVal'];
               
            if(!$is_su){
		$client_id_temp = $_SESSION['lm_auth']['client_id'];
		if(!empty($client_id_temp)){
        		$client_id_arr = explode("#",$client_id_temp);
                        
                    foreach($client_id_arr as $client_id_single){
                            $client_ids.=$client_id_single.',';	
                    }
                    $client_id = rtrim($client_ids,',');
                }
            }


		$where = (!$is_su)?" gsm_number IN( ".$client_id." ) AND test_data=0 ": " 1 ";
		if($sortVal=='today'){
			
			$calls_date = date('Y-m-d');
			$where.= " AND call_start LIKE '%".$calls_date."%'";
			
		}elseif($sortVal=='yesterday'){
			
			$calls_date = date('Y-m-d',strtotime("-1 days"));
			$where.= " AND call_start LIKE '%".$calls_date."%'";
			
		}elseif($sortVal=='last_7_days'){
			
			$calls_date_from = date('Y-m-d',strtotime("-7 days"));
			$calls_date_to = date('Y-m-d');
			$where.= " AND call_start > '".$calls_date_from."' AND call_start <= '".$calls_date_to."'";
			
		}elseif($sortVal=='last_30_days'){
			
			$calls_date_from = date('Y-m-d',strtotime("-30 days"));
			$calls_date_to = date('Y-m-d');
			$where.= " AND call_start >= '".$calls_date_from."' AND call_start <= '".$calls_date_to."'";
			
		}elseif($sortVal=='this_month'){
			
			$calls_date_from = date('Y-m-01'); // hard-coded '01' for first day
			$calls_date_to  = date('Y-m-t');
			$where.= " AND call_start >= '".$calls_date_from."' AND call_start <= '".$calls_date_to."'";
			
		}elseif($sortVal=='last_month'){
			
			$calls_date_from = date("Y-n-j", strtotime("first day of previous month"));
			$calls_date_to  = date("Y-n-j", strtotime("last day of previous month"));
			$where.= " AND call_start >= '".$calls_date_from."' AND call_start <= '".$calls_date_to."'";
			
		}elseif($sortVal=='lifetime'){
			
			$lifeTimeCheck.= "lifetimecalls";
			
		}else{
			
			$sort_arr = explode("#",$sortVal);
			$timestamp_from = strtotime($sort_arr[0]);
			$calls_date_from = date("Y-m-d", $timestamp_from);	
			$timestamp_to = strtotime($sort_arr[1]);
			$calls_date_to = date("Y-m-d", $timestamp_to);
			
			$where.= " AND call_start >= '".$calls_date_from."' AND call_start <= '".$calls_date_to."'";
		}
		
		if($lifeTimeCheck =='lifetimecalls'){
			$qry_calls = "SELECT * FROM calls WHERE $where $calls_data_limit_clause
                            $calls_data_limit_clause
                                    ORDER BY call_start DESC"; 
                        if($is_su){
                            $qry_calls = "SELECT * FROM calls ORDER BY call_start DESC";
                        }
			$res_calls = $db->Execute($qry_calls);
		}else{
			$qry_calls = "SELECT * FROM calls WHERE ".$where." $calls_data_limit_clause
                            ORDER BY call_start DESC"; 
			$res_calls = $db->Execute($qry_calls);	
		}

		$totalcountCalls =  $res_calls->RecordCount();
		
		?>

            <table class="table table-bordered datatable" id="table-4">
              <thead>
                <tr>
                  <th>Sr.</th>
                  <th>Caller Number</th>
                  <th>Time</th>
                  <th>Duration</th>
                  <th>Date</th>
                  <?php if($is_su): ?><th>Mark as test data</th><?php endif; ?>
                </tr>
              </thead>
              <tbody>
 
			  <?php 
              if($totalcountCalls>0){
                  $i=0;
                  $totalDuration=0;
                  while(!$res_calls->EOF){
                    $i++;
                    
                    $call_start = new DateTime($res_calls->fields['call_start']);
                    $call_end = new DateTime($res_calls->fields['call_end']);
                    $totalDuration+= date_diff($call_start,$call_end);
                    
              ?>
                    <tr class="even gradeA">
                      <td><?php echo $i;?></td>
                      <td><?php echo $res_calls->fields['callerid'];?></td>
                      <td><?php echo timeInAmPm($res_calls->fields['call_start']);?></td>
                      <td>
					  <?php //echo timeDifferance($res_calls->fields['call_start'],$res_calls->fields['call_end']);
						  if($res_calls->fields['call_end']>0){
							echo "Successfully transferred";  
						  }else{
							echo "Busy";  
						  }
					  ?>
                      </td>
                      <td><?php echo userdate($res_calls->fields['call_start']);?></td>
                      <?php if($is_su): ?>
                      <td align="center"><input type="checkbox" name="hide" value="<?php echo $res_calls->fields['id']; ?>" title="Mark as test data" onClick="return toggleHide(this,<?php echo $res_calls->fields['id']; ?>);"></td>
                      <?php endif; ?>
                    </tr>
                
                <?php
                    $res_calls->MoveNext();
                  }
              }
                ?>

          </tbody>
          <tfoot>
            <tr>
              <th>Sr.</th>
              <th>Caller Number</th>
              <th>Time</th>
              <th>Duration</th>
              <th>Date</th>
              <?php if($is_su): ?><th>Mark as test data</th><?php endif; ?>
            </tr>
            
          </tfoot>
        </table>

        <?php
		
	}
	
	
	//################################//
	//##### Custom range Calls ######//
	//##############################//
	
	if($_GET['act']=='prepareCallsGraph'){
            
            $client_check = "";
            if(!$is_su){
		$client_id_temp = $_SESSION['lm_auth']['client_id'];
		
		$client_id_arr = explode("#",$client_id_temp);
		foreach($client_id_arr as $client_id_single){
			$client_ids.=$client_id_single.',';	
		}
		$client_id = rtrim($client_ids,',');
                $client_check = " AND gsm_number IN( ".$client_id." )  AND test_data=0 ";
            }
		$sortVal = $_GET['sortVal'];
		$where = '';
			
		$sort_arr = explode("#",$sortVal);
		$timestamp_from = strtotime($sort_arr[0]);
		$calls_date_from = date("Y-m-d", $timestamp_from);	
		$timestamp_to = strtotime($sort_arr[1]);
		$calls_date_to = date("Y-m-d", $timestamp_to);
			
		$where.= " call_start >= '".$calls_date_from."' AND call_start <= '".$calls_date_to."' $client_check";
		
		$qry_calls = "SELECT * FROM calls WHERE ".$where." $calls_data_limit_clause ORDER BY call_start DESC"; 
		$res_calls = $db->Execute($qry_calls);
		$totalcountCalls =  $res_calls->RecordCount();
		
		?>
		<script type="text/javascript">
        $.noConflict();
                    (function($, window, undefined)
        {
            "use strict";
            
            $(document).ready(function($)
            {
                
                // Morris.js Graphs
                if(typeof Morris != 'undefined')
                {
                    
                    Morris.Line({
                        element: 'chart_custom_range',
                        data: custom_range_data,
                        xkey: 'elapsed',
                        ykeys: ['value'],
                        labels: ['Calls'],
                        parseTime: false,
                        lineColors: ['#242d3c']
                    });
                }
            });
            
        })(jQuery, window);
                 
        function data(offset) {
            var ret = [];
            for (var x = 0; x <= 360; x += 10) {
                var v = (offset + x) % 360;
                ret.push({
                    x: x,
                    y: Math.sin(Math.PI * v / 180).toFixed(4),
                    z: Math.cos(Math.PI * v / 180).toFixed(4),
                });
            }
            return ret;
        }
         
         
        function getRandomInt(min, max) 
        {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        
        
        var custom_range_data = [
            <?php 
                $arr_calls = createDateRangeArray($calls_date_from,$calls_date_to);
                $i=0;
                foreach($arr_calls as $arr_call){
                    $i++;
                    $qry_calls_month = "SELECT count(*) as month_calls_total FROM calls WHERE call_start LIKE '%".$arr_call."%' $client_check $calls_data_limit_clause"; 
                    $res_calls_month = $db->Execute($qry_calls_month);
            ?>
            
                        {"elapsed": "<?php echo userdate($arr_call);?>", "value": <?php echo $res_calls_month->fields['month_calls_total'];?>} <?php if($i<count($arr_calls)){ echo ",";}?>
                        
            <?php }?>
                    ];
                    
        </script>
		<div id="chart_custom_range"></div>
        <?php
		
	}
	
	
	//################################//
	//####### Lifetime Calls ########//
	//##############################//
	
	if($_GET['act']=='prepareLifeTimeCallsGraph'){
            $is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
            if(!$is_su){
		$client_id_temp = $_SESSION['lm_auth']['client_id'];
		
		$client_id_arr = explode("#",$client_id_temp);
		foreach($client_id_arr as $client_id_single){
			$client_ids.=$client_id_single.',';	
		}
		$client_id = rtrim($client_ids,',');
            }
		
		?>
		<script type="text/javascript">
        $.noConflict();
                    (function($, window, undefined)
        {
            "use strict";
            
            $(document).ready(function($)
            {
                // Morris.js Graphs
                if(typeof Morris != 'undefined')
                {
                    
                    Morris.Line({
                        element: 'chart_life_time',
                        data: life_time_data,
                        xkey: 'elapsed',
                        ykeys: ['value'],
                        labels: ['Calls'],
                        parseTime: false,
                        lineColors: ['#242d3c']
                    });
                }
            });
            
        })(jQuery, window);
        
        
                    
        function data(offset) {
            var ret = [];
            for (var x = 0; x <= 360; x += 10) {
                var v = (offset + x) % 360;
                ret.push({
                    x: x,
                    y: Math.sin(Math.PI * v / 180).toFixed(4),
                    z: Math.cos(Math.PI * v / 180).toFixed(4),
                });
            }
            return ret;
        }
         
         
        function getRandomInt(min, max) 
        {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        
        
        var life_time_data = [
            <?php 
            $where = "";
            if(!$is_su){
                $where = " AND gsm_number IN( ".$client_id." )  AND test_data=0 ";
            }
                $calls_date_from = '2014-09-22';
                $calls_date_to = date('Y-m-d');
                $arr_calls = createDateRangeArray($calls_date_from,$calls_date_to);
                $i=0;
                foreach($arr_calls as $arr_call){
                    $i++;
                    $qry_calls_month = "SELECT count(*) as month_calls_total FROM calls WHERE call_start LIKE '%".$arr_call."%' $where $calls_data_limit_clause"; 
                    $res_calls_month = $db->Execute($qry_calls_month);
            ?>
            
                        {"elapsed": "<?php echo userdate($arr_call);?>", "value": <?php echo $res_calls_month->fields['month_calls_total'];?>} <?php if($i<count($arr_calls)){ echo ",";}?>
                        
            <?php }?>
                    ];
                    
                    
        </script>
		<div id="chart_life_time"></div>
        <?php
		
	}
	
	
	
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//****************************************************************** Emails Section ***************************************************//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	//###############################//
	//######## Emails Listing ######//
	//#############################//
	
	if($_GET['act']=='doSelectedSortEmail'){
            $is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
            $where = " 1 ";
            
            if(!$is_su){
		$client_id = $_SESSION['lm_auth']['tbl_id'];
		$sortVal = $_GET['sortVal'];
		$where = " client_id='".$client_id."' AND test_data=0 ";
            }
            $sortVal = $_GET['sortVal'];
		if($sortVal=='today'){
			
			$emails_date = date('Y-m-d');
			$where.= " AND email_date LIKE '%".$emails_date."%'";
			
		}elseif($sortVal=='yesterday'){
			
			$emails_date = date('Y-m-d',strtotime("-1 days"));
			$where.= " AND email_date LIKE '%".$emails_date."%'";
			
		}elseif($sortVal=='last_7_days'){
			
			$emails_date_from = date('Y-m-d',strtotime("-7 days"));
			$emails_date_to = date('Y-m-d');
			$where.= " AND email_date > '".$emails_date_from."' AND email_date <= '".$emails_date_to."'";
			
		}elseif($sortVal=='last_30_days'){
			
			$emails_date_from = date('Y-m-d',strtotime("-30 days"));
			$emails_date_to = date('Y-m-d');
			$where.= " AND email_date >= '".$emails_date_from."' AND email_date <= '".$emails_date_to."'";
			
		}elseif($sortVal=='this_month'){
			
			$emails_date_from = date('Y-m-01'); // hard-coded '01' for first day
			$emails_date_to  = date('Y-m-t');
			$where.= " AND email_date >= '".$emails_date_from."' AND email_date <= '".$emails_date_to."'";
			
		}elseif($sortVal=='last_month'){
			
			$emails_date_from = date("Y-n-j", strtotime("first day of previous month"));
			$emails_date_to  = date("Y-n-j", strtotime("last day of previous month"));
			$where.= " AND email_date >= '".$emails_date_from."' AND email_date <= '".$emails_date_to."'";
			
		}elseif($sortVal=='lifetime'){
			
			$lifeTimeCheck.= "lifetimeemails";
			
		}else{
			
			$sort_arr = explode("#",$sortVal);
			$timestamp_from = strtotime($sort_arr[0]);
			$emails_date_from = date("Y-m-d", $timestamp_from);	
			$timestamp_to = strtotime($sort_arr[1]);
			$emails_date_to = date("Y-m-d", $timestamp_to);
			
			$where.= " AND email_date >= '".$emails_date_from."' AND email_date <= '".$emails_date_to."'";
		}
		if($lifeTimeCheck =='lifetimeemails'){
			$qry_emails = "SELECT * FROM emails WHERE client_id='".$client_id."'  AND test_data=0 
                                     ORDER BY email_date DESC"; 
                        if($is_su){
                            $qry_emails = "SELECT * FROM emails ORDER BY email_date DESC";
                        }
			$res_emails = $db->Execute($qry_emails);
		}else{
			$qry_emails = "SELECT * FROM emails WHERE ".$where."  ORDER BY email_date DESC"; 
			$res_emails = $db->Execute($qry_emails);
		}
                //echo $qry_emails;
		$totalCountEmails =  $res_emails->RecordCount();
		?>

            <table class="table table-bordered datatable" id="table-4">
  <thead>
    <tr>
      <th>Email No.</th>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Message</th>
      <th>Date</th>
      <?php if($is_su): ?><th>Mark as test data</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
  <?php
  if($totalCountEmails>0){
	  $i=0;
	  while(!$res_emails->EOF){
		$i++;
                $checked = ($res_emails->fields['test_data']==1)?' checked':'';
  ?>  
    <tr class="gradeA">
      <td><?php echo $i;?></td>
      <td><?php echo $res_emails->fields['name']?></td>
      <td><?php echo $res_emails->fields['email'];?></td>
      <td><?php echo $res_emails->fields['phone'];?></td>
      <td><?php echo smart_wordwrap($res_emails->fields['message'],40);?></td>
      <td><?php echo userdate($res_emails->fields['email_date']);?></td>
      <?php if($is_su): ?><td align="center"><input type="checkbox" name="hide" value="<?php echo $res_emails->fields['id']; ?>" title="Mark as test data" onClick="return toggleHide(this,<?php echo $res_emails->fields['id']; ?>);" <?php echo $checked; ?>></td><?php endif; ?>
    </tr>
  <?php
  		$res_emails->MoveNext();
	  }
  }
  ?>
  </tbody>
  <tfoot>
    <tr>
      <th>Email No.</th>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Message</th>
      <th>Date</th>
      <?php if($is_su): ?><th>Mark as test data</th><?php endif; ?>
    </tr>
  </tfoot>
</table>

        <?php
		
	}
	
	
	//################################//
	//### Custom range Emails #######//
	//##############################//
	
	if($_GET['act']=='prepareEmailsGraph'){
            $is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
		$client_id = $_SESSION['lm_auth']['tbl_id'];
		$where = " client_id='".$client_id."'";
		$sortVal = $_GET['sortVal'];
		
                if($is_su) $where = " 1 AND ";
                else $where = " test_data=0 AND ";
			
		$sort_arr = explode("#",$sortVal);
		$timestamp_from = strtotime($sort_arr[0]);
		$emails_date_from = date("Y-m-d", $timestamp_from);	
		$timestamp_to = strtotime($sort_arr[1]);
		$emails_date_to = date("Y-m-d", $timestamp_to);
			
		$where.= " email_date >= '".$emails_date_from."' AND email_date <= '".$emails_date_to."' AND $where";
		
		$qry_emails = "SELECT * FROM emails WHERE ".$where." ORDER BY email_date DESC"; 
		$res_emails = $db->Execute($qry_emails);
		$totalcountEmails =  $res_emails->RecordCount();
		
		?>
		<script type="text/javascript">
        $.noConflict();
                    (function($, window, undefined)
        {
            "use strict";
            
            $(document).ready(function($)
            {
                
                // Morris.js Graphs
                if(typeof Morris != 'undefined')
                {
                    
                    Morris.Line({
                        element: 'chart_custom_range',
                        data: custom_range_data,
                        xkey: 'elapsed',
                        ykeys: ['value'],
                        labels: ['Emails'],
                        parseTime: false,
                        lineColors: ['#242d3c']
                    });
				}
            });
            
        })(jQuery, window);
                
        function data(offset) {
            var ret = [];
            for (var x = 0; x <= 360; x += 10) {
                var v = (offset + x) % 360;
                ret.push({
                    x: x,
                    y: Math.sin(Math.PI * v / 180).toFixed(4),
                    z: Math.cos(Math.PI * v / 180).toFixed(4),
                });
            }
            return ret;
        }
         
         
        function getRandomInt(min, max) 
        {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        
        
        var custom_range_data = [
            <?php 
                $arr_emails = createDateRangeArray($emails_date_from,$emails_date_to);
                $i=0;
                foreach($arr_emails as $arr_email){
                    $i++;
                    $qry_emails_month = "SELECT count(*) as month_emails_total FROM emails WHERE email_date LIKE '%".$arr_email."%' AND $where"; 
                    $res_emails_month = $db->Execute($qry_emails_month);
            ?>
            
                        {"elapsed": "<?php echo userdate($arr_email);?>", "value": <?php echo $res_emails_month->fields['month_emails_total'];?>} <?php if($i<count($arr_emails)){ echo ",";}?>
                        
            <?php }?>
                    ];
                    
        </script>
        <div id="chart_custom_range"></div>
        <?php
		
	}
	
	
	//################################//
	//###### Life time Emails #######//
	//##############################//
	
	if($_GET['act']=='prepareLifeTimeEmailsGraph'){
            $is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
            
            if(!$is_su){
		$client_id = $_SESSION['lm_auth']['tbl_id'];
            }
		?>
		<script type="text/javascript">
        var $ = jQuery.noConflict();
                    (function($, window, undefined)
        {
            "use strict";
            
            $(document).ready(function($)
            {
                // Morris.js Graphs
                if(typeof Morris != 'undefined')
                {	
                    Morris.Line({
                        element: 'chart_life_time',
                        data: life_time_data,
                        xkey: 'elapsed',
                        ykeys: ['value'],
                        labels: ['Emails'],
                        parseTime: false,
                        lineColors: ['#242d3c']
                    });
                    
                }
            });
            
        })(jQuery, window);
        
        function data(offset) {
            var ret = [];
            for (var x = 0; x <= 360; x += 10) {
                var v = (offset + x) % 360;
                ret.push({
                    x: x,
                    y: Math.sin(Math.PI * v / 180).toFixed(4),
                    z: Math.cos(Math.PI * v / 180).toFixed(4),
                });
            }
            return ret;
        }
         
         
        function getRandomInt(min, max) 
        {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
        
        
        var life_time_data = [
            <?php 
			
            if(!$is_su){
                $where = "client_id='".$client_id."'  AND test_data=0 ";
            } else {
                $where = " 1 ";
            }
                $emails_date_from = '2014-09-01';
                $emails_date_to = date('Y-m-d');
                $arr_emails = createDateRangeArray($emails_date_from,$emails_date_to);
                $i=0;
                foreach($arr_emails as $arr_email){
                    $i++;
                    $qry_emails_month = "SELECT count(*) as month_emails_total FROM emails WHERE email_date LIKE '%".$arr_email."%' AND $where"; 
                    $res_emails_month = $db->Execute($qry_emails_month);
            ?>
            
                        {"elapsed": "<?php echo userdate($arr_email);?>", "value": <?php echo $res_emails_month->fields['month_emails_total'];?>} <?php if($i<count($arr_emails)){ echo ",";}?>
                        
            <?php }?>
                    ];
                    
                    
        </script>
		<div id="chart_life_time"></div>
        <?php
		
	}
	
	
	
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//****************************************************************** ROI Section ******************************************************//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	
	//################################//
	//###### Custome range ROI ######//
	//##############################//
	
	
	if($_GET['act']=='prepareRoiGraph'){
            $is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
		$client_id = $_SESSION['lm_auth']['table_id'];
		$sortVal = $_GET['sortVal'];
		$where = '';
			
                $client_check = " 1 ";
                if(!$is_su){
                    $client_check = " client_id = '".$client_id."' AND test_data=0 ";
                }
		$sort_arr = explode("#",$sortVal);
		$timestamp_from = strtotime($sort_arr[0]);
		$roi_date_from = date("Y-m-d", $timestamp_from);	
		$timestamp_to = strtotime($sort_arr[1]);
		$roi_date_to = date("Y-m-d", $timestamp_to);
			
		$where.= " roi_date >= '".$roi_date_from."' AND roi_date <= '".$roi_date_to."' AND $client_check";
		
		$qry_roi = "SELECT * FROM roi WHERE ".$where; 
		$res_roi = $db->Execute($qry_roi);
		$totalcountRoi =  $res_roi->RecordCount();
		
		?>
		<script type="text/javascript">
        var $ = jQuery.noConflict();
                    (function($, window, undefined)
        {
            "use strict";
            
            $(document).ready(function($)
            {
            
                // Morris.js Graphs
                if(typeof Morris != 'undefined')
                {
                    
                    Morris.Area({
                        element: 'chart_custom_range',
                        data: [
                        
                        <?php 
                            $arr_rois = createDateRangeArray($roi_date_from,$roi_date_to);
                            $i=0;
                            foreach($arr_rois as $arr_roi){
                                $i++;
                    
                                $qry_roi_month = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$arr_roi."%' AND $client_check"; 
                                $res_roi_month = $db->Execute($qry_roi_month);
                                $roi_total = $res_roi_month->fields['total_lifetime'];
                                if($roi_total==''){
                                    $roi_total=0;
                                }
                            ?>
                            { y: '<?php echo $arr_roi;?>', a: <?php echo $roi_total;?>}<?php if($i<count($arr_rois)){ echo ",";}?>
                        
                            <?php }?>
                        ],
                        xkey: 'y',
                        ykeys: ['a'],
                        labels: ['ROI']
                    });
                    
                    
                }
            });
            
        })(jQuery, window);
          
        function data(offset) {
            var ret = [];
            for (var x = 0; x <= 360; x += 10) {
                var v = (offset + x) % 360;
                ret.push({
                    x: x,
                    y: Math.sin(Math.PI * v / 180).toFixed(4),
                    z: Math.cos(Math.PI * v / 180).toFixed(4),
                });
            }
            return ret;
        }
         
         
        function getRandomInt(min, max) 
        {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
                    
        </script>
        <div id="chart_custom_range"></div>
        <?php
		
	}
	
	
	//################################//
	//######## Life time ROI ########//
	//##############################//
	
	if($_GET['act']=='prepareLifeTimeRoiGraph'){
            $is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
            
		$client_id = $_SESSION['lm_auth']['table_id'];
		//$qry_roi_graph = "SELECT * FROM roi WHERE client_id='".$client_id."' order by id asc limit 1";
		//$res_roi_graph = $db->Execute($qry_roi_graph);
		//$totalcountRoiGraph =  $res_roi_graph->RecordCount();
		
		?>
		<script type="text/javascript">
        $.noConflict();
                    (function($, window, undefined)
        {
            "use strict";
            
            $(document).ready(function($)
            {
            
                // Morris.js Graphs
                if(typeof Morris != 'undefined')
                {
                    
                    // Area Chart
                    Morris.Area({
                        element: 'chart_life_time',
                        data: [
                            <?php
				$period   = getMonths('2014-09-22', date('Y-m-d'));
				$ii=0;

                                $client_check = " 1 ";
                                if(!$is_su){
                                    $client_check = "client_id='".$client_id."' AND test_data=0 ";
                                }
				foreach ($period as $dt) {
    				$yearmonth = $dt->format("Y-m");
	
					$qry_roi_month = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$yearmonth."%' AND $client_check";
					$res_roi_month = $db->Execute($qry_roi_month);
					$roi_total = $res_roi_month->fields['total_lifetime'];
					if($roi_total==''){
						$roi_total=0;	
					}
					?>
					{ y: '<?php echo $yearmonth?>', a: <?php echo $roi_total?>},
					<?php
					$ii++;
				}
				?>
                        ],
                        xkey: 'y',
                        ykeys: ['a'],
                        labels: ['ROI']
                    });
                }
            });
            
        })(jQuery, window);
                    
        function data(offset) {
            var ret = [];
            for (var x = 0; x <= 360; x += 10) {
                var v = (offset + x) % 360;
                ret.push({
                    x: x,
                    y: Math.sin(Math.PI * v / 180).toFixed(4),
                    z: Math.cos(Math.PI * v / 180).toFixed(4),
                });
            }
            return ret;
        }
         
         
        function getRandomInt(min, max) 
        {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }
                    
                    
        </script>
        <div id="chart_life_time"></div>
        <?php
		
	}
	
	
	
	if($_GET['act']=='prepareLeadsGraph'){
	
            $is_su = ($_SESSION['lm_auth']['account_type']=='super')?true:false;
		$sortVal = $_GET['sortVal'];
		$client_id = $_SESSION['lm_auth']['tbl_id'];
		if($sortVal=='today'){
			
		?>
        
        	<script type="text/javascript">
jQuery(document).ready(function($) 
{
	
	
	
	// Area Chart
	var area_chart_demo = $("#area-chart-today");
	
	area_chart_demo.parent().show();
	
	var area_chart = Morris.Area({
		element: 'area-chart-today',
		data: [
		
		<?php
                $client_check = " 1 ";
                $client_check_email = " 1 ";
                if(!$is_su){
                    $client_check = "client_id='".$client_id."' AND test_data=0 ";
                    $client_check_email .= " AND test_data=0 ";
                }
		
                $qry_em_month = "SELECT count(*) as  total_lifetime_emails FROM emails WHERE email_date LIKE '%".date('Y-m-d')."%' AND $client_check_email";
		$res_em_month = $db->Execute($qry_em_month);
		$em_total = $res_em_month->fields['total_lifetime_emails'];
		if($em_total==''){
			$em_total=0;	
		}
		
		$qry_ca_month = "SELECT count(*) as  total_lifetime_calls FROM calls WHERE call_start LIKE '%".date('Y-m-d')."%' AND $client_check $calls_data_limit_clause";
		$res_ca_month = $db->Execute($qry_ca_month);
		$ca_total = $res_ca_month->fields['total_lifetime_calls'];
		if($ca_total==''){
			$ca_total=0;	
		}
		
		?>
		{ y: '<?php echo date('Y-m-d')?>', a:<?php echo $ca_total?> , b:<?php echo $em_total?> , c:<?php echo $ca_total+$em_total?>  },
		<?php
		//echo $yearmonth."--".$em_total."<br>";
	
	
	?>	
			
		],
		xkey: 'y',
		ykeys: ['a', 'b', 'c'],
		labels: ['Calls','Emails' ,'Leads' ],
		lineColors: ['#00a65a','#00c0ef' , '#f56954'],
                xLabelFormat: function(y) { 
                    var month = new Array();
                    month[0] = "January";month[1] = "February";month[2] = "March";
                    month[3] = "April";month[4] = "May";month[5] = "June";
                    month[6] = "July";month[7] = "August";month[8] = "September";
                    month[9] = "October";month[10] = "November";month[11] = "December";
                    return month[(y.getMonth())]+' '+y.getDay()+', '+y.getFullYear();
                }
	});
	
	area_chart_demo.parent().attr('style', '');
	
	/*setInterval( function() {
		random.removeData(seriesData);
		random.addData(seriesData);
		graph.update();
	
	}, 500 );*/
});


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
}
</script>
<div class="tab-pane active" id="area-chart">
                <div id="area-chart-today" class="morrischart" style="height: 300px"></div>
              </div>
              
        <?php
			
		}elseif($sortVal=='yesterday'){
			
			$yesterday_date = date('Y-m-d',strtotime("-1 days"));
			
			?>
			<script type="text/javascript">
jQuery(document).ready(function($) 
{
	
	
	// Area Chart
	var area_chart_demo = $("#area-chart-yesterday");
	
	area_chart_demo.parent().show();
	
	var area_chart = Morris.Area({
		element: 'area-chart-yesterday',
		data: [
		
		<?php
                
                $client_check = " 1 ";
                $client_check_email = " 1 ";
                if(!$is_su){
                    $client_check = "client_id='".$client_id."' AND test_data=0 ";
                    $client_check_email .= " AND test_data=0 ";
                }
                
		$qry_em_month = "SELECT count(*) as  total_lifetime_emails FROM emails WHERE email_date LIKE '%".$yesterday_date."%' AND $client_check_email";
		$res_em_month = $db->Execute($qry_em_month);
		$em_total = $res_em_month->fields['total_lifetime_emails'];
		if($em_total==''){
			$em_total=0;	
		}
		
		$qry_ca_month = "SELECT count(*) as  total_lifetime_calls FROM calls WHERE call_start LIKE '%".$yesterday_date."%' AND $client_check $calls_data_limit_clause";
		$res_ca_month = $db->Execute($qry_ca_month);
		$ca_total = $res_ca_month->fields['total_lifetime_calls'];
		if($ca_total==''){
			$ca_total=0;	
		}
		
		?>
		{ y: '<?php echo $yesterday_date?>', a:<?php echo $ca_total?> , b:<?php echo $em_total?> , c:<?php echo $ca_total+$em_total?>  },
		<?php
		//echo $yearmonth."--".$em_total."<br>";
	?>	
			
		],
		xkey: 'y',
		ykeys: ['a', 'b', 'c'],
		labels: ['Calls','Emails' ,'Leads' ],
		lineColors: ['#00a65a','#00c0ef' , '#f56954'],
                xLabelFormat: function(y) { 
                    var month = new Array();
                    month[0] = "January";month[1] = "February";month[2] = "March";
                    month[3] = "April";month[4] = "May";month[5] = "June";
                    month[6] = "July";month[7] = "August";month[8] = "September";
                    month[9] = "October";month[10] = "November";month[11] = "December";
                    return month[(y.getMonth())]+' '+y.getFullYear();
                }
	});
	
	area_chart_demo.parent().attr('style', '');
	
	/*setInterval( function() {
		random.removeData(seriesData);
		random.addData(seriesData);
		graph.update();
	
	}, 500 );*/
});


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
}
</script>
<div class="tab-pane active" id="area-chart">
                <div id="area-chart-yesterday" class="morrischart" style="height: 300px"></div>
              </div>
			<?php
		}elseif($sortVal=='last_7_days'){
			
			$calls_date_from = date('Y-m-d',strtotime("-7 days"));
			$calls_date_to = date('Y-m-d');
			
			?>
			<script type="text/javascript">
jQuery(document).ready(function($) 
{
	
	
	// Area Chart
	var area_chart_demo = $("#area-chart-7days");
	
	area_chart_demo.parent().show();
	
	var area_chart = Morris.Area({
		element: 'area-chart-7days',
		data: [
		
		<?php 
                
                $client_check = " 1 ";
                $client_check_email = " 1 ";
                if(!$is_su){
                    $client_check = "client_id='".$client_id."' AND test_data=0 ";
                    $client_check_email .= " AND test_data=0 ";
                }
                
                $arr_calls = createDateRangeArray($calls_date_from,$calls_date_to);
                $i=0;
                foreach($arr_calls as $arr_call){
                    $i++;
                    $qry_em_month = "SELECT count(*) as  total_lifetime_emails FROM emails WHERE email_date LIKE '%".$arr_call."%' AND $client_check_email";
		$res_em_month = $db->Execute($qry_em_month);
		$em_total = $res_em_month->fields['total_lifetime_emails'];
		if($em_total==''){
			$em_total=0;	
		}
		
		$qry_ca_month = "SELECT count(*) as  total_lifetime_calls FROM calls WHERE call_start LIKE '%".$arr_call."%' AND $client_check $calls_data_limit_clause";
		$res_ca_month = $db->Execute($qry_ca_month);
		$ca_total = $res_ca_month->fields['total_lifetime_calls'];
		if($ca_total==''){
			$ca_total=0;	
		}
            ?>
            
             { y: '<?php echo $arr_call?>', a:<?php echo $ca_total?> , b:<?php echo $em_total?> , c:<?php echo $ca_total+$em_total?>  },
                        
            <?php }?>
			
		
			
		],
		xkey: 'y',
		ykeys: ['a', 'b', 'c'],
		labels: ['Calls','Emails' ,'Leads' ],
		lineColors: ['#00a65a','#00c0ef' , '#f56954'],
                xLabelFormat: function(y) { 
                    var month = new Array();
                    month[0] = "January";month[1] = "February";month[2] = "March";
                    month[3] = "April";month[4] = "May";month[5] = "June";
                    month[6] = "July";month[7] = "August";month[8] = "September";
                    month[9] = "October";month[10] = "November";month[11] = "December";
                    return month[(y.getMonth())]+' '+y.getFullYear();
                }
	});
	
	area_chart_demo.parent().attr('style', '');
	
	/*setInterval( function() {
		random.removeData(seriesData);
		random.addData(seriesData);
		graph.update();
	
	}, 500 );*/
});


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
}
</script>
<div class="tab-pane active" id="area-chart">
                <div id="area-chart-7days" class="morrischart" style="height: 300px"></div>
              </div>
			<?php
		}
		
		
		elseif($sortVal=='last_30_days'){
			
			$calls_date_from = date('Y-m-d',strtotime("-30 days"));
			$calls_date_to = date('Y-m-d');
			
			?>
			<script type="text/javascript">
jQuery(document).ready(function($) 
{
	
	
	
	// Area Chart
	var area_chart_demo = $("#area-chart-30days");
	
	area_chart_demo.parent().show();
	
	var area_chart = Morris.Area({
		element: 'area-chart-30days',
		data: [
		
		<?php 
                
                $client_check = " 1 ";
                $client_check_email = " 1 ";
                if(!$is_su){
                    $client_check = "client_id='".$client_id."' AND test_data=0 ";
                    $client_check_email .= " AND test_data=0 ";
                }
                
                $arr_calls = createDateRangeArray($calls_date_from,$calls_date_to);
                $i=0;
                foreach($arr_calls as $arr_call){
                    $i++;
                    $qry_em_month = "SELECT count(*) as  total_lifetime_emails FROM emails WHERE email_date LIKE '%".$arr_call."%' AND $client_check_email";
		$res_em_month = $db->Execute($qry_em_month);
		$em_total = $res_em_month->fields['total_lifetime_emails'];
		if($em_total==''){
			$em_total=0;	
		}
		
		$qry_ca_month = "SELECT count(*) as  total_lifetime_calls FROM calls WHERE call_start LIKE '%".$arr_call."%' AND $client_check $calls_data_limit_clause";
		$res_ca_month = $db->Execute($qry_ca_month);
		$ca_total = $res_ca_month->fields['total_lifetime_calls'];
		if($ca_total==''){
			$ca_total=0;	
		}
            ?>
            
             { y: '<?php echo $arr_call?>', a:<?php echo $ca_total?> , b:<?php echo $em_total?> , c:<?php echo $ca_total+$em_total?>  },
                        
            <?php }?>
			
		
			
		],
		xkey: 'y',
		ykeys: ['a', 'b', 'c'],
		labels: ['Calls','Emails' ,'Leads' ],
		lineColors: ['#00a65a','#00c0ef' , '#f56954'],
                xLabelFormat: function(y) { 
                    var month = new Array();
                    month[0] = "January";month[1] = "February";month[2] = "March";
                    month[3] = "April";month[4] = "May";month[5] = "June";
                    month[6] = "July";month[7] = "August";month[8] = "September";
                    month[9] = "October";month[10] = "November";month[11] = "December";
                    return month[(y.getMonth())]+' '+y.getFullYear();
                }
	});
	
	area_chart_demo.parent().attr('style', '');
	
	/*setInterval( function() {
		random.removeData(seriesData);
		random.addData(seriesData);
		graph.update();
	
	}, 500 );*/
});


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
}
</script>
<div class="tab-pane active" id="area-chart">
                <div id="area-chart-30days" class="morrischart" style="height: 300px"></div>
              </div>
			<?php
		}
		
		elseif($sortVal=='this_month'){
			
			$calls_date_from = date('Y-m-01'); // hard-coded '01' for first day
			$calls_date_to  = date('Y-m-t');
			
			?>
			<script type="text/javascript">
jQuery(document).ready(function($) 
{
	
	
	
	// Area Chart
	var area_chart_demo = $("#area-chart-thismonth");
	
	area_chart_demo.parent().show();
	
	var area_chart = Morris.Area({
		element: 'area-chart-thismonth',
		data: [
		
		<?php 
                $arr_calls = createDateRangeArray($calls_date_from,$calls_date_to);
                $i=0;
                
                $client_check = " 1 ";
                $client_check_email = " 1 ";
                if(!$is_su){
                    $client_check = "client_id='".$client_id."' AND test_data=0 ";
                    $client_check_email .= " AND test_data=0 ";
                }
                
                foreach($arr_calls as $arr_call){
                    $i++;
                    $qry_em_month = "SELECT count(*) as  total_lifetime_emails FROM emails WHERE email_date LIKE '%".$arr_call."%' AND $client_check_email";
		$res_em_month = $db->Execute($qry_em_month);
		$em_total = $res_em_month->fields['total_lifetime_emails'];
		if($em_total==''){
			$em_total=0;	
		}
		
		$qry_ca_month = "SELECT count(*) as  total_lifetime_calls FROM calls WHERE call_start LIKE '%".$arr_call."%' AND $client_check $calls_data_limit_clause";
		$res_ca_month = $db->Execute($qry_ca_month);
		$ca_total = $res_ca_month->fields['total_lifetime_calls'];
		if($ca_total==''){
			$ca_total=0;	
		}
            ?>
            
             { y: '<?php echo $arr_call?>', a:<?php echo $ca_total?> , b:<?php echo $em_total?> , c:<?php echo $ca_total+$em_total?>  },
                        
            <?php }?>
			
		
			
		],
		xkey: 'y',
		ykeys: ['a', 'b', 'c'],
		labels: ['Calls','Emails' ,'Leads' ],
		lineColors: ['#00a65a','#00c0ef' , '#f56954'],
                xLabelFormat: function(y) { 
                    var month = new Array();
                    month[0] = "January";month[1] = "February";month[2] = "March";
                    month[3] = "April";month[4] = "May";month[5] = "June";
                    month[6] = "July";month[7] = "August";month[8] = "September";
                    month[9] = "October";month[10] = "November";month[11] = "December";
                    return month[(y.getMonth())]+' '+y.getFullYear();
                }
	});
	
	area_chart_demo.parent().attr('style', '');
	
	/*setInterval( function() {
		random.removeData(seriesData);
		random.addData(seriesData);
		graph.update();
	
	}, 500 );*/
});


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
}
</script>
<div class="tab-pane active" id="area-chart">
                <div id="area-chart-thismonth" class="morrischart" style="height: 300px"></div>
              </div>
			<?php
		}
		
		elseif($sortVal=='last_month'){
			
			$calls_date_from = date("Y-n-j", strtotime("first day of previous month"));
			$calls_date_to  = date("Y-n-j", strtotime("last day of previous month"));
			
			?>
			<script type="text/javascript">
jQuery(document).ready(function($) 
{
	
	
	
	// Area Chart
	var area_chart_demo = $("#area-chart-lastmonth");
	
	area_chart_demo.parent().show();
	
	var area_chart = Morris.Area({
		element: 'area-chart-lastmonth',
		data: [
		
		<?php 
                
                
                $client_check = " 1 ";
                $client_check_email = " 1 ";
                if(!$is_su){
                    $client_check = "client_id='".$client_id."' AND test_data=0 ";
                    $client_check_email .= " AND test_data=0 ";
                }
                
                $arr_calls = createDateRangeArray($calls_date_from,$calls_date_to);
                $i=0;
                foreach($arr_calls as $arr_call){
                    $i++;
                    $qry_em_month = "SELECT count(*) as  total_lifetime_emails FROM emails WHERE email_date LIKE '%".$arr_call."%' AND $client_check_email";
		$res_em_month = $db->Execute($qry_em_month);
		$em_total = $res_em_month->fields['total_lifetime_emails'];
		if($em_total==''){
			$em_total=0;	
		}
		
		$qry_ca_month = "SELECT count(*) as  total_lifetime_calls FROM calls WHERE call_start LIKE '%".$arr_call."%' AND $client_check $calls_data_limit_clause";
		$res_ca_month = $db->Execute($qry_ca_month);
		$ca_total = $res_ca_month->fields['total_lifetime_calls'];
		if($ca_total==''){
			$ca_total=0;	
		}
            ?>
            
             { y: '<?php echo $arr_call?>', a:<?php echo $ca_total?> , b:<?php echo $em_total?> , c:<?php echo $ca_total+$em_total?>  },
                        
            <?php }?>
			
		
			
		],
		xkey: 'y',
		ykeys: ['a', 'b', 'c'],
		labels: ['Calls','Emails' ,'Leads' ],
		lineColors: ['#00a65a','#00c0ef' , '#f56954'],
                xLabelFormat: function(y) { 
                    var month = new Array();
                    month[0] = "January";month[1] = "February";month[2] = "March";
                    month[3] = "April";month[4] = "May";month[5] = "June";
                    month[6] = "July";month[7] = "August";month[8] = "September";
                    month[9] = "October";month[10] = "November";month[11] = "December";
                    return month[(y.getMonth())]+' '+y.getFullYear();
                }
	});
	
	area_chart_demo.parent().attr('style', '');
	
	/*setInterval( function() {
		random.removeData(seriesData);
		random.addData(seriesData);
		graph.update();
	
	}, 500 );*/
});


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
}
</script>
<div class="tab-pane active" id="area-chart">
                <div id="area-chart-lastmonth" class="morrischart" style="height: 300px"></div>
              </div>
			<?php
		}
		
		elseif($sortVal=='lifetime'){
			  
			?>
			<script type="text/javascript">
jQuery(document).ready(function($) 
{
	
	
	
	// Area Chart
	var area_chart_demo = $("#area-chart-lifetime");
	
	area_chart_demo.parent().show();
	
	var area_chart = Morris.Area({
		element: 'area-chart-lifetime',
		data: [
		
		<?php
	$periods   = getMonths('2014-09-22', date('Y-m-d'));
	$ii=0;

	foreach($periods as $dt){
            
                
                $client_check = " 1 ";
                $client_check_email = " 1 ";
                if(!$is_su){
                    $client_check = "client_id='".$client_id."' AND test_data=0 ";
                    $client_check_email .= " AND test_data=0 ";
                }
                
		$yearmonth = $dt->format("Y-m");
		$ii++;
                
		$qry_em_month = "SELECT count(*) as  total_lifetime_emails FROM emails WHERE email_date LIKE '%".$yearmonth."%' AND $client_check_email ";
		$res_em_month = $db->Execute($qry_em_month);
		$em_total = $res_em_month->fields['total_lifetime_emails'];
		if($em_total==''){
			$em_total=0;	
		}
		
		$qry_ca_month = "SELECT count(*) as  total_lifetime_calls FROM calls WHERE call_start LIKE '%".$yearmonth."%' AND $client_check $calls_data_limit_clause";
		$res_ca_month = $db->Execute($qry_ca_month);
		$ca_total = $res_ca_month->fields['total_lifetime_calls'];
		if($ca_total==''){
			$ca_total=0;	
		}
		
		?>
		{ y: '<?php echo $yearmonth?>', a:<?php echo $ca_total?> , b:<?php echo $em_total?> , c:<?php echo $ca_total+$em_total?>  },
		<?php
		//echo $yearmonth."--".$em_total."<br>";
	
	}
	?>
			
		
			
		],
		xkey: 'y',
		ykeys: ['a', 'b', 'c'],
		labels: ['Calls','Emails' ,'Leads' ],
		lineColors: ['#00a65a','#00c0ef' , '#f56954'],
                xLabelFormat: function(y) { 
                    var month = new Array();
                    month[0] = "January";month[1] = "February";month[2] = "March";
                    month[3] = "April";month[4] = "May";month[5] = "June";
                    month[6] = "July";month[7] = "August";month[8] = "September";
                    month[9] = "October";month[10] = "November";month[11] = "December";
                    return month[(y.getMonth())]+' '+y.getFullYear();
                }
	});
	
	area_chart_demo.parent().attr('style', '');
	
	/*setInterval( function() {
		random.removeData(seriesData);
		random.addData(seriesData);
		graph.update();
	
	}, 500 );*/
});


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
}
</script>
<div class="tab-pane active" id="area-chart">
                <div id="area-chart-lifetime" class="morrischart" style="height: 300px"></div>
              </div>
			<?php
		}
		
		else{
			
			$sortVal = $_GET['sortVal'];
			
			$sort_arr = explode("#",$sortVal);
			$timestamp_from = strtotime($sort_arr[0]);
			$calls_date_from = date("Y-m-d", $timestamp_from);	
			$timestamp_to = strtotime($sort_arr[1]);
			$calls_date_to = date("Y-m-d", $timestamp_to);
			
			?>
			<script type="text/javascript">
jQuery(document).ready(function($) 
{
	
	
	// Area Chart
	var area_chart_demo = $("#area-chart-customrange");
	
	area_chart_demo.parent().show();
	
	var area_chart = Morris.Area({
		element: 'area-chart-customrange',
		data: [
		
		<?php 
                $arr_calls = createDateRangeArray($calls_date_from,$calls_date_to);
                $i=0;
                
                $client_check = " 1 ";
                $client_check_email = " 1 ";
                if(!$is_su){
                    $client_check = "client_id='".$client_id."' AND test_data=0 ";
                    $client_check_email .= " AND test_data=0 ";
                }
                
                foreach($arr_calls as $arr_call){
                    $i++;
                    $qry_em_month = "SELECT count(*) as  total_lifetime_emails FROM emails WHERE email_date LIKE '%".$arr_call."%' AND $client_check_email";
		$res_em_month = $db->Execute($qry_em_month);
		$em_total = $res_em_month->fields['total_lifetime_emails'];
		if($em_total==''){
			$em_total=0;	
		}
		
		$qry_ca_month = "SELECT count(*) as  total_lifetime_calls FROM calls WHERE call_start LIKE '%".$arr_call."%' AND $client_check $calls_data_limit_clause";
		$res_ca_month = $db->Execute($qry_ca_month);
		$ca_total = $res_ca_month->fields['total_lifetime_calls'];
		if($ca_total==''){
			$ca_total=0;	
		}
            ?>
            
             { y: '<?php echo $arr_call?>', a:<?php echo $ca_total?> , b:<?php echo $em_total?> , c:<?php echo $ca_total+$em_total?>  },
                        
            <?php }?>
			
		
			
		],
		xkey: 'y',
		ykeys: ['a', 'b', 'c'],
		labels: ['Calls','Emails' ,'Leads' ],
		lineColors: ['#00a65a','#00c0ef' , '#f56954'],
                xLabelFormat: function(y) { 
                    var month = new Array();
                    month[0] = "January";month[1] = "February";month[2] = "March";
                    month[3] = "April";month[4] = "May";month[5] = "June";
                    month[6] = "July";month[7] = "August";month[8] = "September";
                    month[9] = "October";month[10] = "November";month[11] = "December";
                    return month[(y.getMonth())]+' '+y.getFullYear();
                }
	});
	
	area_chart_demo.parent().attr('style', '');
	
	/*setInterval( function() {
		random.removeData(seriesData);
		random.addData(seriesData);
		graph.update();
	
	}, 500 );*/
});


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
}
</script>
<div class="tab-pane active" id="area-chart">
                <div id="area-chart-customrange" class="morrischart" style="height: 300px"></div>
              </div>
			<?php
		}
		
	}
        
        /*
         * Show hide call records from client
         */
        if($_GET['act']=='showHideCall'){
            if($is_su){
                $hide = ($_GET['hide']=='true')?1:0;
                $call_id = (int)$_GET['call_id'];
                $q = "UPDATE calls set test_data = $hide WHERE id=$call_id";
                $r = $db->Execute($q);
                if($r) echo '1';
                else echo '0';
            } else echo '0';
        }
        
        /*
         * Show hide email records from client
         */
        if($_GET['act']=='showHideEmail'){
            if($is_su){
                $hide = ($_GET['hide']=='true')?1:0;
                $email_id = (int)$_GET['email_id'];
                $q = "UPDATE emails set test_data = $hide WHERE id=$email_id";
                $r = $db->Execute($q);
                if($r) echo '1';
                else echo '0';
            } else echo '0';
        }
        
}
?>