<?php



?>
<script type="text/javascript">

    function setSortVal(val){
        var sortCriterean = '';
        var date_from,date_to;
        
        if(val=='Today'){
            sortCriterean = 'today';
//            showSMSData(sortCriterean);
        }else if(val=='Yesterday'){
			//alert ("P");
            sortCriterean = 'yesterday';
//            showSMSData(sortCriterean);
        }else if(val=='Last 7 Days'){
            sortCriterean = 'last_7_days';
//            showSMSData(sortCriterean);
        }else if(val=='Last 30 Days'){
            sortCriterean = 'last_30_days';
//            showSMSData(sortCriterean);
        }else if(val=='This Month'){
            sortCriterean = 'this_month';
//            showSMSData(sortCriterean);
        }else if(val=='Last Month'){
            sortCriterean = 'last_month';
//            showSMSData(sortCriterean);
        }else if(val=='Lifetime'){
            sortCriterean = 'lifetime';
//            showSMSData(sortCriterean);
        }
        else{
            sortCriterean='custom';
//            showSMSDataCustom(date_from,date_to);
        }
        date_from = document.getElementById("daterangepicker_start").value;
        date_to = document.getElementById("daterangepicker_end").value;
        
        showSMSData(sortCriterean,date_from,date_to);
        loadSMSData(sortCriterean,date_from,date_to);
        saveDateRange(sortCriterean,date_from,date_to);
        
//        if(sortCriterean=='custom'){
//            loadSMSDataCustom(date_from,date_to);
//        }
//        else loadSMSData(sortCriterean);
        
        $('#share_report_period').val(sortCriterean);
        $('#share_report_period_from').val(date_from);
        $('#share_report_period_to').val(date_to);
    }
 
 
 
    var period="lifetime";
    var sms_datatable;
    var chart;
    var saved_date_period = '<?php echo $LM_PERIOD; ?>';
    var saved_date_from = '<?php echo $LM_PERIOD_FROM ?>';
    var saved_date_to = '<?php echo $LM_PERIOD_TO ?>';
    
    jQuery(document).ready(
    
        
        function(){
            
            $('#share_report_type').val('sms');
            
            //fill table
            $.ajax({
                url:"<?php echo SURL ?>ajaxresponse/ajax2.php",
                type:"GET",
                cache:false,
                dataType:'json',
                data:{act:"get_sms_table_data",period:saved_date_period,from:saved_date_from,to:saved_date_to},
                success: function(data){
                    var row="";
                    $.each(data, function(index,element){
                        row+="<tr>";
                        row+="<td>"+element.callerid+"</td>";
                        row+="<td>"+element.gsm_number+"</td>";
                        row+="<td>"+element.forward_number+"</td>";
                        row+="<td>"+element.sms+"</td>";
                        row+="<td>"+element.sms_dt+"</td>";
                        row+="</tr>";                         
                    });
                    
                    //console.log(row);
                    $('#table-sms').find('tbody:last').append(row);
                    //console.log(data);
                    sms_datatable = $('#table-sms').dataTable({
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "oTableTools": {
                        },
                        "order": [[ 3, "desc" ]]
                    });
                }
            });
            
            
            //configure and fill chart
            chart = Morris.Line({
                element: 'sms_chart',
                data: [0, 0],
                xkey: 'elapsed',
                ykeys: ['value'],
                labels: ['SMS'],
                parseTime: false,
                lineColors: ['#242d3c']
            });
            
            showSMSData(saved_date_period,saved_date_from,saved_date_to);
            
            
        }
    );
    
 
 
 function loadSMSData(period,from,to){
        $.ajax({
                url:"<?php echo SURL ?>ajaxresponse/ajax2.php",
                type:"GET",
                cache:false,
                dataType:'json',
                data:{act:"get_sms_table_data",period:period,from:from,to:to},
                success: function(data){
                    //console.log("data is "+data);
                    $('#table-sms').dataTable().fnClearTable();
                    $.each(data, function(index,element){   
                        
                        $('#table-sms').dataTable().fnAddData([
                            element.callerid,
                            element.gsm_number,
                            element.forward_number,
                            element.sms,
                            element.sms_dt
                        ]);
                    });
                    
                    //$('#table-sms').dataTable();
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
    else if(period=='custom') title = "Custom Range";
    return title;
}

 function showSMSData(_period,from,to){
    if(_period=='') _period='lifetime';
    
    $.ajax({
            url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
            type: "GET",
            cache:"false",
            data:{act:'get_sms_data',period:_period,from:from,to:to},
            dataType: 'json',dataType: 'json',
            beforeSend : function(){
                $('#sms-chart').addClass('chart-loader');
            },
            success: function(data){
                
                chart.setData(data);
                var title = getTitleFromPeriod(_period);
                $("#ch_title").text(title+ " SMS Breakup");
                $('#sms-chart').removeClass('chart-loader');
            }
        });
}

	
</script>






<div class="row">
    <div class="col-sm-4">
        <ol class="breadcrumb bc-3">
            <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
            <li class="active"> <strong>SMS</strong> </li>
        </ol>
    </div>
    <form>
        <div class="col-sm-4 fr">
            <div class="daterange daterange-inline add-ranges" data-format="MMMM D, YYYY" data-start-date="<?php echo $LM_PERIOD_FROM;?>" data-end-date="<?php echo $LM_PERIOD_TO;?>"> <i class="entypo-calendar"></i> <span><?php echo $LM_PERIOD_FROM;?> - <?php echo $LM_PERIOD_TO;?></span> </div>
        </div>
    </form>
    <div class="clearfix"></div>
</div>




<h2>Recent SMSs</h2>
<br />
<div class="row">
    <div class="col-md-12">
        <div id="sortResult">
            <table class="table table-bordered datatable tbl-mng-sms" id="table-sms">
                <thead>
                    <th>Caller ID</th>
                    <th>GSM</th>
                    <th>Forward Number</th>
                    <th>SMS</th>
                    <th>SMS Date</th>
                </thead>
                <tbody>

                </tbody>

            </table>
        </div>
    </div>
</div>

<br>
<h2 id="ch_title">Breakup</h2>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">Chart</div>
            </div>
            <div id="sms_chart" class="chart-loader" style="height: 300px;"></div>
        </div>
    </div>
</div>


<?php include("bodies/recommend.php"); ?>