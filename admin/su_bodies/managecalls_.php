<?php
if (!defined('SU'))
    die();


$qry_calls = "SELECT * FROM calls ORDER BY call_start DESC ";
$res_calls = $db->Execute($qry_calls);
$totalcountCalls = $res_calls->RecordCount();


?>
<script type="text/javascript">

    function setSortVal(val){
        var sortCriterean = '';
        
        if(val=='Today'){
            sortCriterean = 'today';
            showCallData(sortCriterean);
        }else if(val=='Yesterday'){
			//alert ("P");
            sortCriterean = 'yesterday';
            showCallData(sortCriterean);
        }else if(val=='Last 7 Days'){
            sortCriterean = 'last_7_days';
            showCallData(sortCriterean);
        }else if(val=='Last 30 Days'){
            sortCriterean = 'last_30_days';
            showCallData(sortCriterean);
        }else if(val=='This Month'){
            sortCriterean = 'this_month';
            showCallData(sortCriterean);
        }else if(val=='Last Month'){
            sortCriterean = 'last_month';
            showCallData(sortCriterean);
        }else if(val=='Lifetime'){
            sortCriterean = 'lifetime';
            showCallData(sortCriterean);
        }
        else{
            var first = document.getElementById("daterangepicker_start").value;
            var secon = document.getElementById("daterangepicker_end").value;
            showCallDataCustom(first,secon);
        }
        
        $.ajax({	
            url: "<?php echo SURL ?>ajaxresponse/ajax.php",
            type: "GET",
            catche:"false",
            data:{act:"doSelectedSort",sortVal:sortCriterean},
            success: function (data) {
                $("#sortResult").html(data);	
                $.noConflict();
				 $('#table-4').dataTable().fnDestroy();
                var table = $("#table-4").dataTable();
			 table.columnFilter({
			<?php 
			$sql = "SELECT name FROM tbl_admin WHERE account_type = 'client'";
			$res = $db->Execute($sql);
			$t = $res->RecordCount();
			$str = "";
			if($t>0){
                    while ($rsa = $res->FetchRow()) {
				    $str .= "'".trim($rsa['name'])."',";
				}
                }
			 ?>
			aoColumns: [ 
					null,
					{ type: "select", values: [<?php echo trim($str,','); ?>]  },
				     null,
				     null,
				     null,
					null,
					null,
					null,
					null  
				]

		}); 
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
            }
        });		
    }
    
    /*
    * show hide call records from client
    */
    function toggleHide(ck,id){
        //alert(ck.checked);alert(id);
        var msg = (ck.checked)?"hide":"show";
        if(confirm('Are you sure you want to '+msg+' this call record from client?')){
            $.ajax({	
                url: "<?php echo SURL ?>ajaxresponse/ajax.php",
                type: "GET",
                catche:"false",
                data:{act:"showHideCall",hide:ck.checked,call_id:id},
                success: function (data) {

                }
                });
                return true;
         } else return false;
    }
    
    
  
	
</script>


<script type="text/javascript">
    
    var chart;
    var table_calls;
    
$(document).ready(function(){


        chart = Morris.Line({
                element: 'chart_life_time2',
                data: [0, 0],
                xkey: 'elapsed',
                ykeys: ['value'],
                labels: ['Calls'],
                parseTime: false,
                lineColors: ['#242d3c']
            });
                
        $.ajax({
            url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
            type: "GET",
            cache:"false",
            data:{act:'get_calls_data',period:'lifetime'},
            dataType: 'json',
            success: function(data){
                
                chart.setData(data);
                
            }
        });
        
        getCallTableRecords("lifetime");

        //init data tables
        table_calls = $('#table-calls').DataTable({
            "columns": [
                { "data": "Client Name" },
                { "data": "Client Number<" },
                { "data": "Numbers" },
                { "data": "Time" },
                { "data": "Duration" },
                { "data": "Date" },
                { "data": "Mark as test data" }
            ],
            "ajax":"<?php echo SURL; ?>ajaxresponse/ajax2.php?act=get_calls_data_table&period=lifetime"
            /*"ajax": {
                "url":"<?php echo SURL; ?>ajaxresponse/ajax2.php?act=get_calls_data_table&period=lifetime",
                "data": function(d){
                    return d;
                }
            }*/
        });

});

function showCallData(_period){
    if(_period=='') _period='lifetime';
    
    $.ajax({
            url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
            type: "GET",
            cache:"false",
            data:{act:'get_calls_data',period:_period},
            dataType: 'json',
            success: function(data){
                
                chart.setData(data);
                var title = getTitleFromPeriod(_period);
                $("#ch_title").text(title+ " Calls Breakup");
            }
        });
}

function getTitleFromPeriod(period){
    var title = "";
    if(period=='lifetime') title = "Lifetime";
    else if(period=='month') title = "Monthly";
    else if(period=='week') title = "Weekly";
    else if(period=='daily') title = "Daily";
    else if(period=='today') title = "Today";
    else if(period=='yesterday') title = "Yesterday";
    else if(period=='last_7_days') title = "Last 7 Days";
    else if(period=='last_30_days') title = "Last 30 Days";
    else if(period=='this_month') title = "This Month";
    else if(period=='last_month') title = "Last Month";
    else if(period=='custom') title = "";
    return title;
}

function showCallDataCustom(_from,_to){
    
    $.ajax({
            url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
            type: "GET",
            cache:"false",
            data:{act:'get_calls_data',period:'custom',from:_from,to:_to},
            dataType: 'json',
            success: function(data){
                
                chart.setData(data);
                
                
                
            }
        });
}


function getCallTableRecords(period){
    if(period=='') period='lifetime';
    $.ajax({
       url:"<?php echo SURL; ?>ajaxresponse/ajax2.php",
       type:"GET",
       cache:false,
       data:{act:'get_calls_table_data',period:period},
       dataType:'json',
       success:function(data){
           console.log(data);
       }
    });
}
                    
</script>




<script type="text/javascript">
   
			
    function data(offset) {
        var ret = [];
        for (var x = 0; x <= 360; x += 10) {
            var v = (offset + x) % 360;
            ret.push({
                x: x,
                y: Math.sin(Math.PI * v / 180).toFixed(4),
                z: Math.cos(Math.PI * v / 180).toFixed(4)
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
            <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
            <li class="active"> <strong>Calls</strong> </li>
        </ol>
    </div>
    <form>
        <div class="col-sm-4 fr">
            <div class="daterange daterange-inline add-ranges" data-format="MMMM D, YYYY" data-start-date="September 16, 2014" data-end-date="September 22, 2014"> <i class="entypo-calendar"></i> <span>September 22, 2014 - <?php echo date('F d, Y', strtotime(date('Y-m-d'))); ?></span> </div>
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
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
            "oTableTools": {
            }/*
            "aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "bStateSave": true ,
			

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
            }*/
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
<h2>Recent Calls</h2>
<br />
<div id="sortResult">
    <table class="table table-bordered datatable tbl-mng-calls2" id="table-1">
        <thead>
            <tr>
                <!--<th>Sr.</th>-->
                <th>Client Name</th>
                <th>GSM</th>
                <th>Client Number</th>
                <th>Time</th>
                <th>Duration</th>
                <th>Date</th>
                <th>Mark as test data</th>
            </tr>
        </thead>
        <tbody>
<?php
if ($totalcountCalls > 0) {
    $i = 0;
    $missed = 0;
    $totalDuration = 0;
    while (!$res_calls->EOF) {
        $i++;

        $call_start = new DateTime($res_calls->fields['call_start']);
        $call_end = new DateTime($res_calls->fields['call_end']);
        $totalDuration+= date_diff($call_start, $call_end);
        ?>
                    <tr class="even gradeA">
                        <!--<td></td>-->
                        <?php
                        $gsm = ltrim($res_calls->fields['gsm_number'], '0');
				    $sql = "SELECT name FROM tbl_admin WHERE client_id LIKE '%".$gsm."' LIMIT 1";
				    $c = $db->Execute($sql); 
				    //$cc = $c->Fetch();
                                    //print_r($c->fields);echo "name : ",$c->fields['name'];echo"<br>";
				    ?>
				    <td><?php echo $c->fields['name']; ?></td>
				    <td><?php echo $res_calls->fields['gsm_number']; ?></td>
                        <td><?php echo $res_calls->fields['callerid']; ?></td>
                        <td><?php echo timeInAmPm($res_calls->fields['call_start']); ?></td>
                        <td>
        <?php
        //echo timeDifferance($res_calls->fields['call_start'],$res_calls->fields['call_end']);
        if ($res_calls->fields['call_end'] > 0) {
            echo "Successfully transferred";
        } else {
            echo "Busy";
            $missed++;
        }
        $checked = ($res_calls->fields['test_data']==1)?' checked':'';
        ?> 
                        </td>
                        <td><?php echo userdate($res_calls->fields['call_start']); ?></td>
                        <td align="center"><input type="checkbox" name="hide" value="<?php echo $res_calls->fields['id']; ?>" title="Mark as test data" onClick="return toggleHide(this,<?php echo $res_calls->fields['id']; ?>);" <?php echo $checked; ?>></td>
                    </tr>

                    <?php
                    $res_calls->MoveNext();
                }
            }
            ?>
        </tbody>
	   <!--<tfoot>
            <tr>
                <th>Sr.</th>
                <th>Client Name</th>
			 <th>Client Number</th>
                <th>Numbers</th>
                <th>Time</th>
                <th>Duration</th>
                <th>Date</th>
                <th>Mark as test data</th>
            </tr>
        </tfoot>-->
    </table>
</div>
<?php
$averageCallDuration = $totalDuration / $totalcountCalls;

$saturday = strtotime("last saturday");
$saturday = date('w', $saturday) == date('w') ? $saturday + 7 * 86400 : $saturday;

$friday = strtotime(date("Y-m-d", $saturday) . " +6 days");

$this_week_sd = date("Y-m-d", $saturday);
$this_week_ed = date("Y-m-d", $friday);

$sql = "SELECT DAYNAME(atr.call_start) as dayname,count(*) as total FROM week_days wd LEFT JOIN ( SELECT * FROM calls WHERE call_start >= '" . $this_week_sd . "' AND call_start <= '" . $this_week_ed . "') atr
    												ON wd.week_day_num = DAYOFWEEK(atr.call_start)
													GROUP BY
  													DAYOFWEEK(atr.call_start)";

$this_week_rec = $db->Execute($sql);

$saturday = 0;
$sunday = 0;
$monday = 0;
$tuesday = 0;
$wednesday = 0;
$thursday = 0;
$friday = 0;

while (!$this_week_rec->EOF) {

    $daynames = $this_week_rec->fields['dayname'];
    $totalcalls = $this_week_rec->fields['total'];

    if ($daynames == 'Saturday') {
        $saturday = $this_week_rec->fields['total'];
    }
    if ($daynames == 'Sunday') {
        $sunday = $this_week_rec->fields['total'];
    }
    if ($daynames == 'Monday') {
        $monday = $this_week_rec->fields['total'];
    }
    if ($daynames == 'Tuesday') {
        $tuesday = $this_week_rec->fields['total'];
    }
    if ($daynames == 'Wednesday') {
        $wednesday = $this_week_rec->fields['total'];
    }
    if ($daynames == 'Thursday') {
        $thursday = $this_week_rec->fields['total'];
    }
    if ($daynames == 'Friday') {
        $friday = $this_week_rec->fields['total'];
    }

    $this_week_rec->MoveNext();
}

$arr = array('Saturday' => $saturday,
    'Sunday' => $sunday,
    'Monday' => $monday,
    'Tuesday' => $tuesday,
    'Wednesday' => $wednesday,
    'Thursday' => $thursday,
    'Friday' => $friday
);

$maxs = array_keys($arr, max($arr));
?>
<script type="text/javascript">
    jQuery(document).ready(function($)
    {
        var table = $("#table-4").dataTable({
            "sPaginationType": "bootstrap",
            "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-sm-6'i><'col-sm-6'p>>",
             "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ){
                var index = iDisplayIndexFull +1;
                $('td:eq(0)',nRow).html(index);
                return nRow;
            },
            "aoColumns": [
              { "bSortable": false },
              null,
              null,null,
                null,
                null,
                null,
              { "bSortable": false }
              ]
			
        });
	   table.columnFilter({
			<?php 
			$sql = "SELECT name FROM tbl_admin WHERE account_type = 'client'";
			$res = $db->Execute($sql);
			$t = $res->RecordCount();
			$str = "";
			if($t>0){
                    while ($rsa = $res->FetchRow()) {
				    $str .= "'".trim($rsa['name'])."',";
				}
                }
			 ?>
			aoColumns: [ 
					null,
					{ type: "select", values: [<?php echo trim($str,','); ?>]  },
				     null,
				     null,
				     null,
					null,
					null,
					null,
					null  
				]

		});	
    });
	
	
    var day_data = [
        {"elapsed": "Saturday", "value": <?php echo $saturday; ?>},
        {"elapsed": "Sunday", "value": <?php echo $sunday; ?>},
        {"elapsed": "Monday", "value": <?php echo $monday; ?>},
        {"elapsed": "Tuesday", "value": <?php echo $tuesday; ?>},
        {"elapsed": "Wednesday", "value": <?php echo $wednesday; ?>},
        {"elapsed": "Thursday", "value": <?php echo $thursday; ?>},
        {"elapsed": "Friday", "value": <?php echo $friday; ?>}
    ];
    //console.log(day_data);
	
	
    var month_data = [
<?php
$maxDays = date('t');
for ($i = 1; $i <= $maxDays; $i++) {
    $dayName = date('Y-m-' . $i);

    $qry_calls_month = "SELECT count(*) as month_calls_total FROM calls WHERE call_start LIKE '%" . $dayName . "%'";
    $res_calls_month = $db->Execute($qry_calls_month);
    ?>
    	
                    {"elapsed": "<?php echo userdate($dayName); ?>", "value": <?php echo $res_calls_month->fields['month_calls_total']; ?>} <?php if ($i < $maxDays) {
        echo ",";
    } ?>
    				
<?php } ?>
            ];
			
            //console.log(month_data);
	
	
	
            var last_month_data = [
<?php
$maxDaysInLastMonth = date("t", mktime(0, 0, 0, date("n") - 1));
$lastMonth = date("n") - 1;
for ($i = 1; $i <= $maxDaysInLastMonth; $i++) {
    $dayName = date('Y-' . $lastMonth . '-' . $i);

    $qry_calls_month = "SELECT count(*) as month_calls_total FROM calls WHERE call_start LIKE '%" . $dayName . "%'";
    $res_calls_month = $db->Execute($qry_calls_month);
    ?>
    	
                    {"elapsed": "<?php echo userdate($dayName); ?>", "value": <?php echo $res_calls_month->fields['month_calls_total']; ?>} <?php if ($i < $maxDaysInLastMonth) {
        echo ",";
    } ?>
    				
<?php } ?>
            ];
			
			

            var last_thirty_data = [
<?php
$calls_date_from = date('Y-m-d', strtotime("-30 days"));
$calls_date_to = date('Y-m-d');
$arr_calls = createDateRangeArray($calls_date_from, $calls_date_to);
$i = 0;
foreach ($arr_calls as $arr_call) {
    $i++;
    $qry_calls_month = "SELECT count(*) as month_calls_total FROM calls WHERE call_start LIKE '%" . $arr_call . "%'";
    $res_calls_month = $db->Execute($qry_calls_month);
    ?>
    	
                    {"elapsed": "<?php echo userdate($arr_call); ?>", "value": <?php echo $res_calls_month->fields['month_calls_total']; ?>} <?php if ($i < count($arr_calls)) {
        echo ",";
    } ?>
    				
<?php } ?>
            ];
			
            //console.log(last_thirty_data);
            var last_seven_data = [
<?php
$calls_date_from = date('Y-m-d', strtotime("-06 days"));
$calls_date_to = date('Y-m-d');
$arr_calls = createDateRangeArray($calls_date_from, $calls_date_to);
$i = 0;
foreach ($arr_calls as $arr_call) {
    $i++;
    $qry_calls_month = "SELECT count(*) as month_calls_total FROM calls WHERE call_start LIKE '%" . $arr_call . "%'";
    $res_calls_month = $db->Execute($qry_calls_month);
    ?>
    	
                    {"elapsed": "<?php echo userdate($arr_call); ?>", "value": <?php echo $res_calls_month->fields['month_calls_total']; ?>} <?php if ($i < count($arr_calls)) {
        echo ",";
    } ?>
    				
<?php } ?>
            ];
			
			
            var last_yesterday_data = [
<?php
$calls_date_from = date('Y-m-d', strtotime("-01 days"));
$calls_date_to = date('Y-m-d');
$arr_calls = createDateRangeArray($calls_date_from, $calls_date_to);
$i = 0;
foreach ($arr_calls as $arr_call) {
    $i++;
    $qry_calls_month = "SELECT count(*) as month_calls_total FROM calls WHERE call_start LIKE '%" . $arr_call . "%'";
    $res_calls_month = $db->Execute($qry_calls_month);
    ?>
    	
                    {"elapsed": "<?php echo userdate($arr_call); ?>", "value": <?php echo $res_calls_month->fields['month_calls_total']; ?>} <?php if ($i < count($arr_calls)) {
        echo ",";
    } ?>
    				
<?php } ?>
            ];
			
			
            var today_data = [
<?php
$calls_date_from = date('Y-m-d');
$calls_date_to = date('Y-m-d');
$arr_calls = createDateRangeArray($calls_date_from, $calls_date_to);
$i = 0;
foreach ($arr_calls as $arr_call) {
    $i++;
    $qry_calls_month = "SELECT count(*) as month_calls_total FROM calls WHERE call_start LIKE '%" . $arr_call . "%'";
    $res_calls_month = $db->Execute($qry_calls_month);
    ?>
    	
                    {"elapsed": "<?php echo userdate($arr_call); ?>", "value": <?php echo $res_calls_month->fields['month_calls_total']; ?>} <?php if ($i < count($arr_calls)) {
        echo ",";
    } ?>
    				
<?php } ?>
            ];
	
</script>



<div id="call-result" style="display: none;">
    <table class="table table-bordered datatable tbl-mng-calls2" id="table-calls">
        <thead>
            <tr>
                <th>Client Name</th>
                <th>GSM</th>
                <th>Caller Number</th>
                <th>Time</th>
                <th>Duration</th>
                <th>Date</th>
                <th>Mark as test data</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $q1 = "SELECT * FROM calls ORDER BY id DESC ";
                $res = $db->Execute($q1);
                while (!$res->EOF){
                    $status = ($res->fields['call_end'] > 0)?"Successfully transferred":"Busy";
                    ?>
                        <tr>
                            <td></td>
                            <td><?php echo $res->fields['gsm_number']; ?></td>
                            <td><?php echo $res->fields['callerid']; ?></td>
                            <td><?php echo $res->fields['call_start']; ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php
                    
                    $res->MoveNext();
                }
            ?>
        </tbody>
    </table>
</div>



<br />
<div class="row">
    <div class="col-lg-12">
        <h2 class="fl" style="margin-top:0px;" id="ch_title">Weekly Calls Breakup</h2>
        <div class="btn-group fr">
            <button type="button" class="btn btn-default" id="dayBtn" onClick="showCallData('daily')">Day</button>
            <button type="button" class="btn btn-default active " id="weekBtn" onClick="showCallData('week')">Week</button>
            <button type="button" class="btn btn-default" id="monthBtn" onClick="showCallData('month')">Monthly</button>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<br />
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <table class="table table-bordered" id="table8">
                <tbody>
                    <tr>
                        <td><strong>Line Chart</strong> <br />
                            <div id="chart_life_time2" style="height: 300px;"></div>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
</div>
<br />
<div class="row">
    <div class="col-sm-4">
        <div class="tile-stats tile-aqua">
            <div class="icon"><i class="entypo-clock"></i></div>
            <div class="num"><?php echo $averageCallDuration; ?> Sec</div>
            <h3>AVERAGE CALL TIME</h3>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="tile-stats tile-green">
            <div class="icon"><i class="entypo-calendar"></i></div>
            <div class="num"><?php echo strtoupper($maxs[0]); ?></div>
            <h3>MOST ACTIVE DAY</h3>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="tile-stats tile-red">
            <div class="icon"><i class="entypo-phone"></i></div>
            <div class="num"><?php echo $missed; ?></div>
            <h3>MISSED CALLS</h3>
        </div>
    </div>
</div>
<br />
<hr />
<br />