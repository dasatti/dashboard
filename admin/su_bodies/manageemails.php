<?php

if (!defined('SU'))
    die();




?>
<script type="text/javascript">

    function setSortVal(val){
        var sortCriterean = '';
        var date_from,date_to;
        
        if(val=='Today'){
            sortCriterean = 'today';
        }else if(val=='Yesterday'){
			//alert ("P");
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
        
        showEmailsData(sortCriterean,date_from,date_to);
        loadEmailsData(sortCriterean,date_from,date_to);
        showWeekdayEmailData(sortCriterean,date_from,date_to);
        saveDateRange(sortCriterean,date_from,date_to);
        
        $('#share_report_period').val(sortCriterean);
        $('#share_report_period_from').val(date_from);
        $('#share_report_period_to').val(date_to);
        
    }
 
 
 
    var period="lifetime";
    var emails_datatable;
    var chart;
    var saved_date_period = '<?php echo $LM_PERIOD; ?>';
    var saved_date_from = '<?php echo $LM_PERIOD_FROM ?>';
    var saved_date_to = '<?php echo $LM_PERIOD_TO ?>';
    
    jQuery(document).ready(
    
        
        function(){
        
            $('#share_report_type').val('emails');
            
            //fill table
            $.ajax({
                url:"<?php echo SURL ?>ajaxresponse/ajax2.php",
                type:"GET",
                cache:false,
                dataType:'json',
                data:{act:"get_emails_table_data",period:saved_date_period,from:saved_date_from,to:saved_date_to},
                success: function(data){
                    var row="";var sno=0;
                    $.each(data, function(index,element){
                        var ch = (element.test_data==1)?'checked':''
                        var mark = "<input type='checkbox' name='hide' value='"+element.id+"' title='Mark as test data' onClick='return toggleHide(this,"+element.id+");' "+ch+">";
                        sno++;
                        row+="<tr>";
                        row+="<td>"+sno+"</td>";
                        row+="<td>"+element.client_name+"</td>";
                        row+="<td>"+element.campaign_name+"</td>";
                        row+="<td>"+element.name+"</td>";
                        row+="<td>"+element.email+"</td>";
                        row+="<td>"+element.phone+"</td>";
                        row+="<td><span class=\"big-text\">"+element.message+"</span></td>";
                        row+="<td>"+element.gender+"</td>";
                        row+="<td>"+element.email_date_ae+"</td>";
                        row+="<td>"+mark+"</td>";
                        row+="</tr>";                         
                    });
                    
                    //console.log(row);
                    $('#table-emails').find('tbody:last').append(row);
                    //console.log(data);
                    emails_datatable = $('#table-emails').dataTable({
                        "sPaginationType": "bootstrap",
                        "sDom": "<'row'<'col-xs-6 col-left'l><'col-xs-6 col-right'<'export-data'T>f>r>t<'row'<'col-xs-6 col-left'i><'col-xs-6 col-right'p>>",
                        "oTableTools": {
                        },
                        "columns": [
                            ,,,,,,,{"visible":false},,,
                          ],    
                        "fnDrawCallback": function( oSettings, json ) {
                            lessTextAll(200);
                          }

                    });
                    $(document).dtToggleCols('#table-emails');
                }
            });
            
            
            //configure and fill chart
            chart = Morris.Line({
                element: 'emails_chart',
                data: [0, 0],
                xkey: 'elapsed',
                ykeys: ['value'],
                labels: ['Emails'],
                parseTime: false,
                lineColors: ['#242d3c']
            });
            
//            if(saved_date_period=='custom'){
//                showEmailsDataCustom(saved_date_from,saved_date_to);
//                showWeekdayEmailDataCustom(saved_date_from,saved_date_to);
//            }else{
//                showEmailsData(saved_date_period);
//                showWeekdayEmailData(saved_date_period);
//            }
            showEmailsData(saved_date_period,saved_date_from,saved_date_to);
            showWeekdayEmailData(saved_date_period,saved_date_from,saved_date_to);
            
        }
    );
    
 
 
 function loadEmailsData(period,from,to){
        $.ajax({
                url:"<?php echo SURL ?>ajaxresponse/ajax2.php",
                type:"GET",
                cache:false,
                dataType:'json',
                data:{act:"get_emails_table_data",period:period,from:from,to:to},
                success: function(data){
                    //console.log("data is "+data);
                    $('#table-emails').dataTable().fnClearTable();
                    var sno=0;
                    $.each(data, function(index,element){   
                        var ch = (element.test_data==1)?'checked':''
                        var mark = "<input type='checkbox' name='hide' value='"+element.id+"' title='Mark as test data' onClick='return toggleHide(this,"+element.id+");' "+ch+">";
                        
                        var msg = "<span class=\"big-text\">"+element.message+"</span>";
                        sno++;
                        $('#table-emails').dataTable().fnAddData([
                            sno,
                            element.client_name,
                            element.campaign_name,
                            element.name,
                            element.email,
                            element.phone,
                            msg,
                            element.gender,
                            element.email_date_ae,
                            mark
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

 function showEmailsData(_period,from,to){
    if(_period=='') _period='lifetime';
    
    $.ajax({
            url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
            type: "GET",
            cache:"false",
            data:{act:'get_emails_data',period:_period,from:from,to:to},
            dataType: 'json',
            beforeSend : function(){
                $('#emails-chart').addClass('chart-loader');
            },
            success: function(data){
                
                chart.setData(data);
                var title = getTitleFromPeriod(_period);
                $("#ch_title").text(title+ " Emails Breakup");
                $('#emails_chart').removeClass('chart-loader');
            }
        });
}

	
        /*
    * show hide call records from client
    */
    function toggleHide(ck,id){
        //alert(ck.checked);alert(id);
        var msg = (ck.checked)?"hide":"show";
        if(confirm('Are you sure you want to '+msg+' this email record from client?')){
            $.ajax({	
                url: "<?php echo SURL ?>ajaxresponse/ajax.php",
                type: "GET",
                catche:"false",
                data:{act:"showHideEmail",hide:ck.checked,email_id:id},
                success: function (data) {

                }
                });
                return true;
         } else return false;
    }
    
    
    
function showWeekdayEmailData(_period,from,to){
    $.ajax({
            url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
            type: "GET",
            cache:"false",
            data:{act:'get_email_data_weekday',period:_period,from:from,to:to},
            dataType: 'json',
            success: function(data){
                //console.log(data);
                var day = data.weekday;
                $('#avg-per-month').text(data.average);
                $('#most-active-day').text(day);
                $('#peak-time').text(data.peak_time);
            }
        });
}


function lessTextAll(max_len){

    $('.big-text').each(function(){
        
        var text = $(this).text();
        if(!text.length>max_len) return;
        text = text.replace("More >>", "");
        var p1 = text.substr(0, max_len);
        var more_link = " <a href=\"#\" class=\"show-more\">More >></a>";
        var p2 = text.substr(max_len,text.length-max_len);
        var more_text = "";
        if(p2.length>0) 
            more_text = "<span class=\"more-text\">"+p2+"</span>"+more_link;
        $(this).html(p1+more_text);
    });
    
    $('.more-text').hide();
    $('.show-more').click(function(){
        var mt = $(this).prev('.more-text');
        $(mt).toggle();
        if($(mt).is(':visible')){
            $(this).text('<< Less');  
        } else if($(mt).is(':hidden')) {
            $(this).text('More >>');
        }
        return false;
    });
}


function lastSync(){
    $.ajax({
        
        url:"<?php echo SURL; ?>crons/cron.log",
        type:"GET",
        data:{},
        success:function(data){
            $('#last_sync_log').text(data);
        }
    })
}

function importEmails(){
    $.ajax({
        url:"<?php echo SURL; ?>crons/import_emails.php",
        type:"GET",
        data:{debug:"false"},
        success:function(data){
            $('#btn_import_emails_label').text("Import done");
            $('#import_email_spinner').removeClass("fa-spinner fa-spin");
            lastSync();
        },
        beforeSend: function(){
            $('#import_email_spinner').addClass("fa-spinner fa-spin");
            $('#btn_import_emails_label').text("Processing..");
        }
        
    })
}

    
</script>






<div class="row">
    <div class="col-sm-4">
        <ol class="breadcrumb bc-3">
            <li> <a href="admin.php?act=dashboard"><i class="entypo-home"></i>Dashboard</a> </li>
            <li class="active"> <strong>Emails</strong> </li>
        </ol>
    </div>
    <form>
        <div class="col-sm-4 fr">
            <div class="daterange daterange-inline add-ranges" data-format="MMMM D, YYYY" data-start-date="<?php echo $LM_PERIOD_FROM;?>" data-end-date="<?php echo $LM_PERIOD_TO;?>"> <i class="entypo-calendar"></i> <span><?php echo $LM_PERIOD_FROM;?> - <?php echo $LM_PERIOD_TO;?></span> </div>
        </div>
    </form>
    <div class="clearfix"></div>
</div>




<h2>Recent Emails</h2>
<span id="last_sync">
<b>Last Synced : </b>
    <span id="last_sync_log">
    <?php
        include "crons/cron.log";   
    ?>
    </span>
</span>
<a href="#" onClick="importEmails();" class="btn btn-blue fr" id="btn_import_emails" style="margin-top:-25px;"><i class="fa" id="import_email_spinner"></i><span id="btn_import_emails_label">Import Emails From Unbounce</span></a> 
<br />
<br />
<div class="row">
    <div class="col-md-12">
        <div id="sortResult">
            <table class="table table-bordered datatable tbl-mng-emails" id="table-emails">
                <thead>
                    <th>#</th>
                    <th>Client Name</th>
                    <th>Campaign</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Gender</th>
                    <th>Date</th>
                    <th>Mark as test</th>
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
            <div id="emails_chart" class="chart-loader" style="height: 300px;"></div>
        </div>
    </div>
</div>

<br />
<div class="row">
  <div class="col-sm-4">
    <div class="tile-stats tile-aqua">
      <div class="icon"><i class="entypo-mail"></i></div>
      <div class="num" id="avg-per-month"><?php echo ceil($total_avg_per_month);?> EMAILS</div>
      <h3>AVERAGE PER MONTH</h3>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="tile-stats tile-green">
      <div class="icon"><i class="entypo-calendar"></i></div>
      <div class="num" id="most-active-day" style="text-transform: uppercase;"></div>
      <h3>MOST ACTIVE DAY</h3>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="tile-stats tile-red">
      <div class="icon"><i class="entypo-clock"></i></div>
      <div class="num" id="peak-time"></div>
      <h3>PEAK TIME</h3>
    </div>
  </div>
</div>
<br />