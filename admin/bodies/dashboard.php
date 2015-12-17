<?php
$client_id = $_SESSION['lm_auth']['tbl_id'];

$client_id_temp = $_SESSION['lm_auth']['client_id'];
$client_id_arr = explode("#",$client_id_temp);
foreach($client_id_arr as $client_id_single){
	$client_ids.=$client_id_single.',';	
}
$client_id_calls = rtrim($client_ids,',');


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



//Graph
$currentYear = date('Y');
$firstYear = $currentYear-1;
$secondYear = $currentYear-2;
$thirdYear = $currentYear-3;
$fourthYear = $currentYear-4;
$fifthYear = $currentYear-5;
$sixthYear = $currentYear-6;

?>
   
<script type="text/javascript">

    var leadsChart ;
    
	function setSortVal(val){
		var sortCriterean = '';
                var date_from;
                var date_to;
                
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
		}
		else if(val=='Lifetime'){
			sortCriterean = 'lifetime';
		}else{
                        sortCriterean = 'custom';
				
		}
                date_from = document.getElementById("daterangepicker_start").value;
                date_to = document.getElementById("daterangepicker_end").value;
  
                showLeadsData(sortCriterean,date_from,date_to);
                prepareLeadsChart(sortCriterean,date_from,date_to);
                getAnalyticsData(sortCriterean,date_from,date_to);
                saveDateRange(sortCriterean,date_from, date_to);
                
	}
	
        
        
        
</script>    
    
                
<script type="text/javascript">
    
    var saved_date_period = '<?php echo $LM_PERIOD; ?>';
    var saved_date_from = '<?php echo $LM_PERIOD_FROM ?>';
    var saved_date_to = '<?php echo $LM_PERIOD_TO ?>';

    jQuery(document).ready(function() 
    {

            showLeadsData(saved_date_period,saved_date_from,saved_date_to);
            getAnalyticsData(saved_date_period,saved_date_from,saved_date_to);

            //Prepare leads chart
            leadsChart = Morris.Line({
                    element: 'leads-chart',
                    data: [],
                    xkey: 'y',
                    ykeys: ['a', 'b', 'c'],
                    labels: ['Calls','Emails' ,'Leads' ],
                    lineColors: ['#00a65a','#00c0ef' , '#f56954']
                  });


//            prepareLeadsChart(saved_date_period,saved_date_from,saved_date_to);
            prepareLeadsChartData30Days();
            
    });



function showLeadsData(period,from,to){
    $.ajax({
        url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
        type: "GET",
        cache:"false",
        data:{act:'get_leads_data',period:period,from:from,to:to},
        dataType: 'json',
        success: function(data){
                //console.log(data.calls_query);
               //console.log(data.email_query);
               
               $("#total_leads").text(data.total_leads);
               $("#total_unique_calls").text(data.total_unique_calls);
               $("#total_unique_emails").text(data.total_unique_emails);
               $("#rep_calls").text(data.total_calls-data.total_unique_calls); //repeting calls
               $("#rep_emails").text(data.total_emails-data.total_unique_emails); //repeting emails
               $("#total_lifetime_leads").text(data.total_leads_lifetime);
               $("#total_lifetime_calls").text(data.total_calls_lifetime);
               $("#total_lifetime_emails").text(data.total_emails_lifetime);
        }
    });
}

function prepareLeadsChart(period,from,to){
    $.ajax({
        url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
        type: "GET",
        cache:"false",
        data:{act:'get_leads_chart_data',period:period,from:from,to:to},
        dataType: 'json',
        beforeSend : function(){
            $('#leads-chart').addClass('chart-loader');
        },
        success: function(_data){
               
            leadsChart.setData(_data);
            $('#leads-chart').removeClass('chart-loader');
            var periodName = getPeriodName(period);
            $('#leads-chart-period').text(periodName);
        }
    });
}

function prepareLeadsChartData30Days(){
    prepareLeadsChart('last_30_days','','');
    var periodName = getPeriodName('last_30_days');
    $('#leads-chart-period').text(periodName);
}


function getAnalyticsData(period,from,to){
    $.ajax({
        url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
        type: "GET",
        cache:"false",
        data:{act:'get_ga_data',period:period,from:from,to:to},
        dataType: 'json',
        success: function(data){
            $('#page-views').text(data.page_views);
            $('#unique-visitors').text(data.unique_visitors);
            //console.log(data);
            //console.log(data.chart_data.rows);
            var chart_add_views = "";
            var chart_unique_views = "";
            $.each(data.chart_data.rows, function( index, value ) {
                //console.log(value[3]);
                chart_add_views+=value[2]+',';
                chart_unique_views+=value[3]+',';
              });
              //console.log(chart_add_views.slice(0,-1));
              //console.log(chart_unique_views.slice(0,-1));
              
              $('.pageviewschart').text(chart_add_views.slice(0,-1));
              $('.uniquevisitorschart').text(chart_unique_views.slice(0,-1));
              
            $('.pageviewschart').sparkline('html', {type: 'bar', height: '30px', barColor: '#ff6264'} );
            $('.uniquevisitorschart').sparkline('html', {type: 'bar', height: '30px', barColor: '#00b19d'} );
        }
    });
}


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

function getPeriodName(period){
    switch(period){
        case 'today' : return "Today's"; break;
        case 'yesterday' : return "Yesterday's"; break;
        case 'last_7_days' : return "Last 7 day"; break;
        case 'last_30_days' : return "Last 30 day"; break;
        case 'this_month' : return "This month"; break;
        case 'last_month' : return "Last month"; break;
        case 'lifetime' : return "Lifetime"; break;
        case 'default' : return ""; break
    }
}



</script>
    
    <div class="row">
  <div class="col-sm-4">
    <ol class="breadcrumb bc-3">
      <li> <a href="javascript:;"><i class="entypo-home"></i>Home</a> </li>
      <li> <a href="javascript:;">Leads</a> </li>
      <li class="active"> <strong>Dashboard</strong> </li>
    </ol>
  </div>

<?php    include 'bodies/request_call.php'; ?>
  <form>
    <div class="col-sm-4 fr">
      <div id="daterangeselector" class="daterange daterange-inline add-ranges" data-format="MMMM D, YYYY" data-start-date="<?php echo $LM_PERIOD_FROM;?>" data-end-date="<?php echo $LM_PERIOD_TO;?>"> <i class="entypo-calendar"></i> <span><?php echo $LM_PERIOD_FROM;?> - <?php echo $LM_PERIOD_TO;?></span> </div>
    </div>
  </form>
  <div class="clearfix"></div>
</div>
    <br />
    <div class="row">
      <div class="col-sm-4"> <a href="javascript:void(0)">
        <div class="tile-stats tile-red">
          <div class="icon"><i class="entypo-chart-bar"></i></div>
          <div class="num" data-start="0" data-end="" data-postfix="" data-duration="1500" data-delay="0" id="total_leads">0</div>
          <h3>Unique Leads</h3>
          <p>Lifetime Leads: <span id="total_lifetime_leads"></span></p>
        </div>
        </a> </div>
      <div class="col-sm-4"> <a href="admin.php?act=managecalls">
        <div class="tile-stats tile-green">
          <div class="icon"><i class="entypo-phone"></i></div>
          <div class="num" data-start="0" data-end="" data-postfix="" data-duration="1500" data-delay="600" id="total_unique_calls">0</div>
          <h3>New Calls</h3>
          <p>
              Repetitive Calls: <span id="rep_calls"></span>, 
              Lifetime Calls: <span id="total_lifetime_calls"></span>
          </p>
        </div>
        </a> </div>
      <div class="col-sm-4"> <a href="admin.php?act=manageemails">
        <div class="tile-stats tile-aqua">
          <div class="icon"><i class="entypo-mail"></i></div>
          <div class="num" data-start="0" data-end="" data-postfix="" data-duration="1500" data-delay="1200" id="total_unique_emails">0</div>
          <h3>New Emails</h3>
          <p>
              Repetitive Emails: <span id="rep_emails"></span>, 
              Lifetime Emails: <span id="total_lifetime_emails"></span>
          </p>
        </div>
        </a> </div>
    </div>
    <br />

    <br />
    <div class="row">
      <div class="col-sm-12">
        <div class="panel panel-primary" id="charts_env">
          <div class="panel-heading">
            <div class="panel-title" id="leads-chart-title"><span id="leads-chart-period"></span> Leads History</div>
            
          </div>
          <div class="panel-body"> 
            <div id="leads-chart" class="morrischart chart-loader" style="height: 300px"></div>
          </div>
          <table class="table table-bordered table-responsive">
            <thead>
              <tr>
                <th width="50%" class="col-padding-1"> <div class="pull-left">
                    <div class="h4 no-margin">Page Views</div>
                    <small id="page-views">0</small> </div>
                  <span class="pull-right pageviewschart"></span> </th>
                <th width="50%" class="col-padding-1"> <div class="pull-left">
                    <div class="h4 no-margin">Unique Visitors</div>
                    <small id="unique-visitors">0</small> </div>
                  <span class="pull-right uniquevisitorschart"></span> </th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
      
        
    </div>

 
   
