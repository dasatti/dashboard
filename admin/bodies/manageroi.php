<?php
$client_id = $_SESSION['lm_auth']['table_id'];
$qry_roi = "SELECT * FROM roi WHERE client_id='".$client_id."'"; 
$res_roi = $db->Execute($qry_roi);
$totalCountRoi =  $res_roi->RecordCount();


$gsm = $_SESSION['lm_auth']['client_id'];
$sql_roi2 = "SELECT avg_value_of_sale,avg_lead_to_sale FROM tbl_admin WHERE client_id LIKE '%".$gsm."%'";
$res_roi2 = $db->Execute($sql_roi2);
$svg_value_of_sale = ($res_roi2->fields['avg_value_of_sale']!='')?$res_roi2->fields['avg_value_of_sale']:0;
$avg_lead_to_sale = ($res_roi2->fields['avg_lead_to_sale']!='')?$res_roi2->fields['avg_lead_to_sale']:0;

// Projected ROI
$qry_roi_proj = "SELECT avg_sale_revenue FROM roi WHERE client_id='".$client_id."' LIMIT 0,10"; 
$res_roi_proj = $db->Execute($qry_roi_proj);

$total_avg_sale_revenue='';
 while(!$res_roi_proj->EOF){
	$total_avg_sale_revenue+=$res_roi_proj->fields['avg_sale_revenue'];
 $res_roi_proj->MoveNext();
 }
// Lifetime ROI
$qry_roi_lifetime = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE client_id='".$client_id."'"; 
$res_roi_lifetime = $db->Execute($qry_roi_lifetime);

//Graph
$currentYear = date('Y');
$firstYear = $currentYear-1;
$secondYear = $currentYear-2;
$thirdYear = $currentYear-3;
$fourthYear = $currentYear-4;
$fifthYear = $currentYear-5;
$sixthYear = $currentYear-6;

// Current Year ROI
$qry_currentYear_roi = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$currentYear."%' AND client_id='".$client_id."'"; 
$res_currentYear_roi = $db->Execute($qry_currentYear_roi);
$totalcountcurrentYearRoi =  $res_currentYear_roi->fields['total_lifetime'];

// First Year Calls
$qry_firstYear_roi = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$firstYear."%' AND client_id='".$client_id."'"; 
$res_firstYear_roi = $db->Execute($qry_firstYear_roi);
$totalcountfirstYearRoi =  $res_firstYear_roi->fields['total_lifetime'];

// Second Year Calls
$qry_secondYear_roi = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$secondYear."%' AND client_id='".$client_id."'"; 
$res_secondYear_roi = $db->Execute($qry_secondYear_roi);
$totalcountsecondYearRoi =  $res_secondYear_roi->fields['total_lifetime'];

// Third Year Calls
$qry_thirdYear_roi = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$thirdYear."%' AND client_id='".$client_id."'"; 
$res_thirdYear_roi = $db->Execute($qry_thirdYear_roi);
$totalcountthirdYearRoi =  $res_thirdYear_roi->fields['total_lifetime'];

// Fourth Year Calls
$qry_fourthYear_roi = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$fourthYear."%' AND client_id='".$client_id."'"; 
$res_fourthYear_roi = $db->Execute($qry_fourthYear_roi);
$totalcountfourthYearRoi =  $res_fourthYear_roi->fields['total_lifetime'];

// Fifth Year Calls
$qry_fifthYear_roi = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$fifthYear."%' AND client_id='".$client_id."'"; 
$res_fifthYear_roi = $db->Execute($qry_fifthYear_roi);
$totalcountfifthYearRoi =  $res_fifthYear_roi->fields['total_lifetime'];

// Sixth Year Calls
$qry_sixthYear_roi = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$sixthYear."%' AND client_id='".$client_id."'"; 
$res_sixthYear_roi = $db->Execute($qry_sixthYear_roi);
$totalcountsixthYearRoi =  $res_sixthYear_roi->fields['total_lifetime'];
?>
<script>


var saved_date_period = '<?php echo $LM_PERIOD; ?>';
var saved_date_from = '<?php echo $LM_PERIOD_FROM ?>';
var saved_date_to = '<?php echo $LM_PERIOD_TO ?>';

$(document).ready(function(){
    calculateROI(saved_date_period,saved_date_from,saved_date_to);
})


function calculateROI(period,from,to){
    $.ajax({
        url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
        type: "GET",
        cache:"false",
        data:{act:'get_roi_data',period:period,from:from,to:to},
        dataType: 'json',
        success: function(data){
            /*
               console.log(data);
               var total_leads = data.total_leads;
               var avg_value_of_sale = <?php echo $svg_value_of_sale; ?>;
               var avg_lead_to_sale = <?php echo $avg_lead_to_sale; ?>;
               
               var roi = avg_value_of_sale * (total_leads*(avg_lead_to_sale/100));
               alert(total_leads);
               if(isNaN(roi)) roi=0;
               $('#projected_roi').text(parseFloat(roi).toFixed(2));
               */
              console.log(data);
              var period_roi = data.period_roi;
              var lifetime_roi = data.lifetime_roi;
              $('#projected_roi').text(parseFloat(period_roi).toFixed(2));
              $('#lifetime_roi').text(parseFloat(lifetime_roi).toFixed(2));
                            
        }
    });
}



</script>

<script type="text/javascript">


	function setSortVal(val){
		
		var sortCriterean = '';
		if(val=='Today'){
			sortCriterean = 'today';
//                        calculateROI(sortCriterean);
			
			$("#chart10").hide();
			$("#chart18").hide();
			$("#chart_seven_data").hide();
			$("#chart_thirty_data").hide();
			$("#chart_last_month").hide();
			$("#chart_yesreday_data").hide();
			$("#chart_today_data").show();
			$("#chart_custom_range").hide();
			$("#chart_life_time").hide();
				
		}else if(val=='Yesterday'){
			sortCriterean = 'yesterday';
//                        calculateROI(sortCriterean);
			
			$("#chart10").hide();
			$("#chart18").hide();
			$("#chart_seven_data").hide();
			$("#chart_thirty_data").hide();
			$("#chart_last_month").hide();
			$("#chart_today_data").hide();
			$("#chart_yesreday_data").show();
			$("#chart_custom_range").hide();
			$("#chart_life_time").hide();
				
		}else if(val=='Last 7 Days'){
			sortCriterean = 'last_7_days';
//                        calculateROI(sortCriterean);
			
			$("#chart10").hide();
			$("#chart18").hide();
			$("#chart_seven_data").show();
			$("#chart_thirty_data").hide();
			$("#chart_last_month").hide();
			$("#chart_yesreday_data").hide();
			$("#chart_today_data").hide();
			$("#chart_custom_range").hide();
			$("#chart_life_time").hide();
				
		}else if(val=='Last 30 Days'){
			sortCriterean = 'last_30_days';
//                        calculateROI(sortCriterean);
			
			$("#chart10").hide();
			$("#chart18").hide();
			$("#chart_seven_data").hide();
			$("#chart_last_month").hide();
			$("#chart_yesreday_data").hide();
			$("#chart_today_data").hide();
			$("#chart_thirty_data").show();
			$("#chart_custom_range").hide();
			$("#chart_life_time").hide();
			
		}else if(val=='This Month'){
			sortCriterean = 'this_month';
//                        calculateROI(sortCriterean);
			
			$("#chart10").hide();
			$("#chart_seven_data").hide();
			$("#chart_last_month").hide();
			$("#chart_thirty_data").hide();
			$("#chart18").show();
			$("#chart_yesreday_data").hide();
			$("#chart_today_data").hide();
			$("#chart_custom_range").hide();
			$("#chart_life_time").hide();
				
		}else if(val=='Last Month'){	
			sortCriterean = 'last_month';
//                        calculateROI(sortCriterean);
			
			$("#chart10").hide();
			$("#chart18").hide();
			$("#chart_seven_data").hide();
			$("#chart_thirty_data").hide();
			$("#chart_last_month").show();
			$("#chart_yesreday_data").hide();
			$("#chart_today_data").hide();
			$("#chart_custom_range").hide();
			$("#chart_life_time").hide();
			
		}else if(val=='Lifetime'){
			sortCriterean = 'lifetime';
//                        calculateROI(sortCriterean);
			
			
			$("#chart10").hide();
			$("#chart18").hide();
			$("#chart_seven_data").hide();
			$("#chart_thirty_data").hide();
			$("#chart_last_month").hide();
			$("#chart_yesreday_data").hide();
			$("#chart_today_data").hide();
			$("#chart_custom_range").hide();
			$("#chart_life_time").show();
			
			noconf.ajax({	
		  		url: "<?php echo SURL?>ajaxresponse/ajax.php",
		  		type: "GET",
		  		catche:"false",
		  		data:{act:"prepareLifeTimeRoiGraph",sortVal:sortCriterean},
		  		success: function (data) {
					noconf("#chart_life_time").html(data);	
		  		}
			});	
			
		}else{
			var first = document.getElementById("daterangepicker_start").value;
			var secon = document.getElementById("daterangepicker_end").value;
			sortCriterean = first+'#'+secon;
//                        calculateROI(sortCriterean);
			
			$("#chart10").hide();
			$("#chart18").hide();
			$("#chart_seven_data").hide();
			$("#chart_thirty_data").hide();
			$("#chart_last_month").hide();
			$("#chart_yesreday_data").hide();
			$("#chart_today_data").hide();
			$("#chart_life_time").hide();
			$("#chart_custom_range").show();
			
			$.ajax({	
		  		url: "<?php echo SURL?>ajaxresponse/ajax.php",
		  		type: "GET",
		  		catche:"false",
		  		data:{act:"prepareRoiGraph",sortVal:sortCriterean},
		  		success: function (data) {
					noconf("#chart_custom_range").html(data);	
					
		  		}
			});	
			
		}
                
                var date_from = document.getElementById("daterangepicker_start").value;
                var date_to = document.getElementById("daterangepicker_end").value;
                calculateROI(sortCriterean,date_from,date_to);
                
                saveDateRange(sortCriterean,date_from,date_to);
				
	}
</script>



<script type="text/javascript">
		;(function($, window, undefined)
{
	"use strict";
	
	$(document).ready(function()
	{	
	
		// Morris.js Graphs
		if(typeof Morris != 'undefined')
		{	
			// Area Chart
			Morris.Area({
				element: 'chart10',
				data: [
				
				<?php
				$periods   = getMonths('2014-09-22', date('Y-m-d'));
				$ii=0;

				foreach($periods as $dt){
    				$yearmonth = $dt->format("Y-m");
					$ii++;
					$qry_roi_month = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$yearmonth."%' AND client_id='".$client_id."'";
					$res_roi_month = $db->Execute($qry_roi_month);
					$roi_total = $res_roi_month->fields['total_lifetime'];
					if($roi_total==''){
						$roi_total=0;	
					}
				?>
					{ y: '<?php echo $yearmonth?>', a: <?php echo $roi_total?>},
				<?php	
				}
				?>
					
				],
				xkey: 'y',
				ykeys: ['a'],
				labels: ['ROI']
			});
			
			
			Morris.Area({
				element: 'chart18',
				data: [
				
				<?php 
					$maxDays=date('t');
					$roi_total=0;
					for($i=1;$i<=$maxDays;$i++){ $dayName = date('Y-m-'.$i);
			
						$qry_roi_month = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$dayName."%'  AND client_id='".$client_id."'"; 
						$res_roi_month = $db->Execute($qry_roi_month);
						$roi_total = $res_roi_month->fields['total_lifetime'];
						if($roi_total==''){
							$roi_total=0;
						}
					?>
					{ y: '<?php echo $dayName;?>', a: <?php echo $roi_total;?>}<?php if($i<$maxDays){ echo ",";}?>
				
					<?php }?>
				],
				xkey: 'y',
				ykeys: ['a'],
				labels: ['ROI']
			});
			
			
			
			Morris.Area({
				element: 'chart_last_month',
				data: [
				
				<?php 
					$maxDaysInLastMonth = date("t", mktime(0,0,0, date("n") - 1));
					$lastMonth = date("n") - 1;
					for($i=1;$i<=$maxDaysInLastMonth;$i++){ $dayName = date('Y-'.$lastMonth.'-'.$i);
			
						$qry_roi_month = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$dayName."%' AND client_id='".$client_id."'"; 
						$res_roi_month = $db->Execute($qry_roi_month);
						$roi_total = $res_roi_month->fields['total_lifetime'];
						if($roi_total==''){
							$roi_total=0;
						}
					?>
					{ y: '<?php echo $dayName;?>', a: <?php echo $roi_total;?>}<?php if($i<$maxDaysInLastMonth){ echo ",";}?>
				
					<?php }?>
				],
				xkey: 'y',
				ykeys: ['a'],
				labels: ['ROI']
			});
			
			Morris.Area({
				element: 'chart_thirty_data',
				data: [
				
				<?php 
					$emails_date_from = date('Y-m-d',strtotime("-30 days"));
					$emails_date_to = date('Y-m-d');
					$arr_emails = createDateRangeArray($emails_date_from,$emails_date_to);
					$i=0;
					foreach($arr_emails as $arr_email){
						$i++;
			
						$qry_roi_month = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$arr_email."%' AND client_id='".$client_id."'"; 
						$res_roi_month = $db->Execute($qry_roi_month);
						$roi_total = $res_roi_month->fields['total_lifetime'];
						if($roi_total==''){
							$roi_total=0;
						}
					?>
					{ y: '<?php echo $arr_email;?>', a: <?php echo $roi_total;?>}<?php if($i<count($arr_emails)){ echo ",";}?>
				
					<?php }?>
				],
				xkey: 'y',
				ykeys: ['a'],
				labels: ['ROI']
			});
			
			
			Morris.Area({
				element: 'chart_seven_data',
				data: [
				
				<?php 
					$emails_date_from = date('Y-m-d',strtotime("-06 days"));
					$emails_date_to = date('Y-m-d');
					$arr_emails = createDateRangeArray($emails_date_from,$emails_date_to);
					$i=0;
					foreach($arr_emails as $arr_email){
						$i++;
			
						$qry_roi_month = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$arr_email."%' AND client_id='".$client_id."'"; 
						$res_roi_month = $db->Execute($qry_roi_month);
						$roi_total = $res_roi_month->fields['total_lifetime'];
						if($roi_total==''){
							$roi_total=0;
						}
					?>
					{ y: '<?php echo $arr_email;?>', a: <?php echo $roi_total;?>}<?php if($i<count($arr_emails)){ echo ",";}?>
				
					<?php }?>
				],
				xkey: 'y',
				ykeys: ['a'],
				labels: ['ROI']
			});
			
			Morris.Area({
				element: 'chart_yesreday_data',
				data: [
				
				<?php 
					$emails_date_from = date('Y-m-d',strtotime("-01 days"));
					$emails_date_to = date('Y-m-d');
					$arr_emails = createDateRangeArray($emails_date_from,$emails_date_to);
					$i=0;
					foreach($arr_emails as $arr_email){
						$i++;
			
						$qry_roi_month = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$arr_email."%' AND client_id='".$client_id."'"; 
						$res_roi_month = $db->Execute($qry_roi_month);
						$roi_total = $res_roi_month->fields['total_lifetime'];
						if($roi_total==''){
							$roi_total=0;
						}
					?>
					{ y: '<?php echo $arr_email;?>', a: <?php echo $roi_total;?>}<?php if($i<count($arr_emails)){ echo ",";}?>
				
					<?php }?>
				],
				xkey: 'y',
				ykeys: ['a'],
				labels: ['ROI']
			});
			
			Morris.Area({
				element: 'chart_today_data',
				data: [
				
				<?php 
					$emails_date_from = date('Y-m-d');
					$emails_date_to = date('Y-m-d');
					$arr_emails = createDateRangeArray($emails_date_from,$emails_date_to);
					$i=0;
					foreach($arr_emails as $arr_email){
						$i++;
			
						$qry_roi_month = "SELECT SUM(avg_sale_revenue) as total_lifetime FROM roi WHERE roi_date LIKE '%".$arr_email."%' AND client_id='".$client_id."'"; 
						$res_roi_month = $db->Execute($qry_roi_month);
						$roi_total = $res_roi_month->fields['total_lifetime'];
						if($roi_total==''){
							$roi_total=0;
						}
					?>
					{ y: '<?php echo $arr_email;?>', a: <?php echo $roi_total;?>}<?php if($i<count($arr_emails)){ echo ",";}?>
				
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
<div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="admin.php?act=manageroi"><i class="entypo-home"></i>Dashboard</a> </li>
      <li> <a href="#">Leads</a> </li>
      <li class="active"> <strong>ROI</strong> </li>
    </ol>
  </div>
<?php    include 'bodies/request_call.php'; ?>
  <form>
    <div class="col-sm-4 fr">
      <div class="daterange daterange-inline add-ranges" data-format="MMMM D, YYYY" data-start-date="<?php echo $LM_PERIOD_FROM;?>" data-end-date="<?php echo $LM_PERIOD_TO;?>"> <i class="entypo-calendar"></i> <span><?php echo $LM_PERIOD_FROM;?> - <?php echo $LM_PERIOD_TO;?></span> </div>
    </div>
  </form>
  <div class="clearfix"></div>
</div>
<script type="text/javascript">
var responsiveHelper;
var breakpointDefinition = {
    tablet: 1024,
    phone : 480
};
var tableContainer;

	jQuery(document).ready(function($)
	{
		tableContainer = $("#table-1");
		
		tableContainer.dataTable({
			"sPaginationType": "bootstrap",
			"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
			"bStateSave": true,
			

		    // Responsive Settings
		    bAutoWidth     : false,
		    fnPreDrawCallback: function () {
		        // Initialize the responsive datatables helper once.
		        if (!responsiveHelper) {
		            responsiveHelper = new ResponsiveDatatablesHelper(tableContainer, breakpointDefinition);
		        }
		    },
		    fnRowCallback  : function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
		        responsiveHelper.createExpandIcon(nRow);
		    },
		    fnDrawCallback : function (oSettings) {
		        responsiveHelper.respond();
		    }
		});
		
		$(".dataTables_wrapper select").select2({
			minimumResultsForSearch: -1
		});
	});
</script>
<script type="text/javascript">
jQuery(window).load(function()
{
	var $ = jQuery;
	
	$("#table-2").dataTable({
		"sPaginationType": "bootstrap",
		"sDom": "t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
		"bStateSave": false,
		"iDisplayLength": 8,
		"aoColumns": [
			{ "bSortable": false },
			null,
			null,
			null,
			null
		]
	});
	
	$(".dataTables_wrapper select").select2({
		minimumResultsForSearch: -1
	});
	
	// Highlighted rows
	$("#table-2 tbody input[type=checkbox]").each(function(i, el)
	{
		var $this = $(el),
			$p = $this.closest('tr');
		
		$(el).on('change', function()
		{
			var is_checked = $this.is(':checked');
			
			$p[is_checked ? 'addClass' : 'removeClass']('highlight');
		});
	});
	
	// Replace Checboxes
	$(".pagination a").click(function(ev)
	{
		replaceCheckboxes();
	});
});
	
// Sample Function to add new row
var giCount = 1;

function fnClickAddRow() 
{
	$('#table-2').dataTable().fnAddData(['<div class="checkbox checkbox-replace"><input type="checkbox" /></div>', giCount+".2", giCount+".3", giCount+".4", giCount+".5" ]);
	
	replaceCheckboxes(); // because there is checkbox, replace it
	
	giCount++;
}
</script>
<script type="text/javascript">
	jQuery(document).ready(function($)
	{
		var table = $("#table-3").dataTable({
			"sPaginationType": "bootstrap",
			"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
			"bStateSave": true
		});
		
		table.columnFilter({
			"sPlaceHolder" : "head:after"
		});
	});
</script>
<div class="row">
  <div class="col-sm-6">
    <div class="tile-stats tile-green">
      <div class="icon"><i class="entypo-suitcase"></i></div>
      <h3>PROJECTD ROI</h3>
      <div class="num"><span id="projected_roi"><?php echo number_format($total_avg_sale_revenue,2);?></span> <small style="font-size:18px;">AED</small></div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="tile-stats tile-aqua">
      <div class="icon"><i class="entypo-suitcase"></i></div>
      <h3>LIFETIME</h3>
      <div class="num"><span id="lifetime_roi"><?php echo number_format($res_roi_lifetime->fields['total_lifetime'],2);?></span> <small style="font-size:18px;">AED</small></div>
    </div>
  </div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($)
	{
		var table = $("#table-4").dataTable({
			"sPaginationType": "bootstrap",
			"sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
			"oTableTools": {
			},
			
		});
	});
		
		
		
		var day_data = [
				{"elapsed": "Saturday", "value": 10},
				{"elapsed": "Sunday", "value": 16},
				{"elapsed": "Monday", "value": 24},
				{"elapsed": "Tuesday", "value": 32},
				{"elapsed": "Wednesday", "value": 26},
				{"elapsed": "Thursday", "value": 22},
				{"elapsed": "Friday", "value": 25}
			];
		
		

</script>
<br />
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-primary">
      <table class="table table-bordered">
        <tbody>
          <tr>
            <td><strong>Projected ROI Breakup</strong> <br />
              <div id="chart10" style="height: 300px"></div>
              <div id="chart18" style="height: 300px;"></div>
              <div id="chart_last_month" style="height: 300px;"></div>
              <div id="chart_thirty_data" style="height: 300px;"></div>
              <div id="chart_seven_data" style="height: 300px;"></div>
              <div id="chart_yesreday_data" style="height: 300px;"></div>
              <div id="chart_today_data" style="height: 300px;"></div>
              <div id="chart_custom_range" style="height: 300px;"></div>
              <div id="chart_life_time" style="height: 300px;"></div>
              </td>
            <td style="display:none;"><strong>Line Chart</strong> <br />
              <div id="chart8" style="height: 300px"></div>
              
              </td>
          </tr>
        </tbody>
      </table>
      <table class="table table-bordered" style="display:none;">
        <tbody>
          <tr>
            <td width="50%"><strong>Bar Charts</strong> <br />
              <div id="chart3" style="height: 250px"></div></td>
            <td><strong>Bars Stacked</strong> <br />
              <div id="chart4" style="height: 250px"></div></td>
          </tr>
        </tbody>
      </table>
      <table class="table table-bordered" style="display:none;">
        <tbody>
          <tr>
            <td width="33%"><strong>Donut Charts</strong> <br />
              <div id="chart5" style="height: 250px"></div></td>
            <td width="33%"><strong>Colored</strong> <br />
              <div id="chart6" style="height: 250px"></div></td>
            <td width="33%"><strong>Formatted</strong> <br />
              <div id="chart7" style="height: 250px"></div></td>
          </tr>
        </tbody>
      </table>
      <table class="table table-bordered" style="display:none;">
        <tbody>
          <tr>
            <td><strong>Line Chart</strong> <br />
              <div id="chart9" style="height: 300px"></div></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
<br />
<hr />
<br />
<!--<script>
        var $j = jQuery;
    </script>
<div class="row">
  <div class="viral-links">
    <h1>We Are Sure That You're Happy With Our Service</h1>
    <a href="javascript:;" onClick="$j('#modal-recommend').modal('show',{backdrop:false});" class="green-btn"><i class="fa fa-thumbs-up"></i>Recommend Us</a></div>
</div>-->

<?php include("bodies/recommend.php"); ?>
<br />

<script type="text/javascript">
	jQuery(document).ready(function($)
	{
		$("#chart18").hide();
		$("#chart_last_month").hide();
		$("#chart_thirty_data").hide();
		$("#chart_seven_data").hide();
		$("#chart_yesreday_data").hide();
		$("#chart_today_data").hide();
		$("#chart_custom_range").hide();
		$("#chart_life_time").hide();
		
	});
</script>