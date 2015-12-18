<?php




?>
<script type="text/javascript">

    function setSortVal(val){
        var sortCriterean = '';
        var date_from,date_to;
        
        if(val=='Today'){
            sortCriterean = 'today';
        }else if(val=='Yesterday'){
            sortCriterean = 'yesterday';
        }else if(val=='Last 7 Days'){
            sortCriterean = 'last_7_days';
        }else if(val=='Last 30 Days'){
            sortCriterean = 'last_30_days';
        }else if(val=='This Month'){
            sortCriterean = 'this_month';
        }else if(val=='Last Month'){
            sortCriterean = 'last_month';
        }else if(val=='Lifetime'){
            sortCriterean = 'lifetime';
        }
        else{
            
            sortCriterean='custom';
        }
        date_from = document.getElementById("daterangepicker_start").value;
        date_to = document.getElementById("daterangepicker_end").value;
       
        showCallsData(sortCriterean,date_from,date_to);
        loadCallsData(sortCriterean,date_from,date_to);
        saveDateRange(sortCriterean,date_from, date_to);
        
        
        $('#share_report_period').val(sortCriterean);
        $('#share_report_period_from').val(date_from);
        $('#share_report_period_to').val(date_to);
        
    }
 
 
 
    var period="lifetime";
    var calls_datatable;
    var chart;
    var saved_date_period = '<?php echo $LM_PERIOD; ?>';
    var saved_date_from = '<?php echo $LM_PERIOD_FROM ?>';
    var saved_date_to = '<?php echo $LM_PERIOD_TO ?>';
    
    jQuery(document).ready(
    
    
        function(){
            
            $('#share_report_type').val('calls');
            //fill table
            $.ajax({
                url:"<?php echo SURL ?>ajaxresponse/ajax2.php",
                type:"GET",
                cache:false,
                dataType:'json',
                data:{act:"get_calls_table_data",period:saved_date_period,from:saved_date_from,to:saved_date_to},
                success: function(data){
                    var row="";var sno=0; var dataFeedback="";
                    $.each(data, function(index,element){
						if(element.feedback =='0'){
							dataFeedback = "<div id=\"feedback_row_"+element.id+"\"><select id=\"feedback_"+element.id+"\"><option value=\"0\">select</option><option value=\"1\">like</option><option value=\"2\">Dislike</option></select><br><div><button onclick=\"updateFeedback("+element.id+")\" class=\"btn btn-ext-setting\" type=\"button\">Save</button></div></div>";
						}else if(element.feedback == "1"){
						var	dataFeedback = "<span class=\"badge badge-success\">Like</span>";
						}else if(element.feedback == '2'){
						var	dataFeedback = "<span class=\"badge badge-primary\">Dislike</span>";
						}
                        sno++;
                        var status = (element.status=='BUSY')?'SUCCESS':element.status;
                        row+="<tr>";
                        row+="<td>"+sno+"</td>";
                        row+="<td>"+element.gsm_number+"</td>";
                        row+="<td>"+element.callerid+"</td>";
                        row+="<td align=center><span class=\"badge badge-success\">"+element.new_call+"</span></td>";
                        row+="<td>"+status+"</td>";
                        row+="<td>"+element.call_date+"</td>";
                        row+="<td>"+element.call_time+"</td>";
                        row+="<td>"+dataFeedback+"</td>";
                        row+="</tr>";                         
                    });
                    
                    //console.log(row);
                    $('#table-calls').find('tbody:last').append(row);
                    //console.log(data);
                    sms_datatable = $('#table-calls').dataTable({
                        "sPaginationType": "bootstrap",
                        //"sDom": "<'data-table-top'l <'export-data'T>r f>t i p",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "oTableTools": {
                        },
                        "order": [[ 6, "desc" ]],
                        "columns":[,{ "visible": false },,,{ "visible": false },,,{"width":"5%","text-align":"center"},]
                    });
                    $(document).dtToggleCols('#table-calls');
                }
            });
            
            
            //configure and fill chart
            chart = Morris.Line({
                element: 'calls_chart',
                data: [0, 0],
                xkey: 'elapsed',
                ykeys: ['value'],
                labels: ['Calls'],
                parseTime: false,
                lineColors: ['#242d3c']
            });
            
            showCallsData(saved_date_period,saved_date_from,saved_date_to);
            //loadCallsData('lifetime');
            
        }
    );
    
   function updateFeedback(id){
	 var optionVal = $("#feedback_"+id).val();

	if(optionVal=='0'){
	   alert("Please select the feedback status like or dilike!"); return;		
	}else{
	if (confirm('Are you sure you want to save this feedback status?')) {
     $.ajax({
                url:"<?php echo SURL ?>ajaxresponse/ajax2.php",
                type:"GET",
                cache:false,
                dataType:'json',
                data:{act:"update_feedback_onCalls",id:id,optionVal:optionVal},
                success: function(data){
						//alert(data['result']);
						if(data['result']=='yes'){
							//alert('Feedback status has been successfully updated');
							if(optionVal=='1'){
								var result = "<span class=\"badge badge-success\">Like</span>";
							}else if(optionVal =='2'){
								var result =  "<span class=\"badge badge-primary\">Dislike</span>";
							}
							$("#feedback_row_"+id).html(result);	return;
						}
				}
	  });
	} else {
    // Do nothing!
	}
	 
	}
	}
 
 function loadCallsData(period,from,to){
        $.ajax({
                url:"<?php echo SURL ?>ajaxresponse/ajax2.php",
                type:"GET",
                cache:false,
                dataType:'json',
                data:{act:"get_calls_table_data",period:period,from:from,to:to},
                success: function(data){
                    //console.log("data is "+data);
                    $('#table-calls').dataTable().fnClearTable();
                    var sno=0;
                    $.each(data, function(index,element){  
                        sno++;
                        var status = (element.status=='BUSY')?'SUCCESS':element.status;
                        $('#table-calls').dataTable().fnAddData([
                            sno,
                            element.gsm_number,
                            element.callerid,
                            '<span class="badge badge-success">'+element.new_call+'</span>',
                            status,
                            element.call_date,
                            element.call_time,
							element.feedback
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

 function showCallsData(_period,from,to){
    if(_period=='') _period='lifetime';
    
    $.ajax({
            url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
            type: "GET",
            cache:"false",
            data:{act:'get_calls_data',period:_period,from:from,to:to},
            dataType: 'json',
            beforeSend : function(){
                $('#calls-chart').addClass('chart-loader');
            },
            success: function(data){
                
                chart.setData(data);
                var title = getTitleFromPeriod(_period);
                $("#ch_title").text(title+ " Calls Breakup");
                $('#calls-chart').removeClass('chart-loader');
            }
        });
}

	
</script>






<div class="row">
    <div class="col-sm-4">
        <ol class="breadcrumb bc-3">
            <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
            <li class="active"> <strong>Calls</strong> </li>
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




<h2>Recent Calls</h2>
<br />
<div class="row">
    <div class="col-md-12">
        <div id="sortResult">
            <table class="table table-bordered datatable tbl-mng-sms" id="table-calls">
                <thead>
                    <th>Sr. No</th>
                    <th>GSM</th>
                    <th>Caller Number</th>
                    <th>New Call?</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Feedback</th>
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
            <div id="calls_chart" class="chart-loader" style="height: 300px;"></div>
        </div>
    </div>
</div>


<?php include("bodies/recommend.php"); ?>