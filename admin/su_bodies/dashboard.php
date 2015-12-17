<?php


if(!defined('SU')) die();



?>
   
<script type="text/javascript">

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
                
//                if(sortCriterean=='custom'){
//                    showLeadsDataCustom(date_from,date_to);
//                    prepareLeadsChartCustom(date_from,date_to);
//                } else{
//                    showLeadsData(sortCriterean);
//                    prepareLeadsChart(sortCriterean);
//                }
                
                showLeadsData(sortCriterean,date_from,date_to);
                prepareLeadsChart(sortCriterean,date_from,date_to);
                saveDateRange(sortCriterean,date_from, date_to);	
	}
	
</script>    
    
                
    <script type="text/javascript">
        
        //global leads chart instance;
        var leadsChart;
        var saved_date_period = '<?php echo $LM_PERIOD; ?>';
        var saved_date_from = '<?php echo $LM_PERIOD_FROM ?>';
        var saved_date_to = '<?php echo $LM_PERIOD_TO ?>';
        
        
        jQuery(document).ready(function($) 
        {


                //Prepare leads chart
                leadsChart = Morris.Line({
                        element: 'leads-chart',
                        data: [],
                        xkey: 'y',
                        ykeys: ['a', 'b', 'c'],
                        labels: ['Calls','Emails' ,'Leads' ],
                        lineColors: ['#00a65a','#00c0ef' , '#f56954']
                      });

//                if(saved_date_period=='custom'){
//                    showLeadsDataCustom(saved_date_from, saved_date_to);
//                    prepareLeadsChartCustom(saved_date_from, saved_date_to);
//                }else {
//                }
                showLeadsData(saved_date_period,saved_date_from, saved_date_to);
                prepareLeadsChart(saved_date_period,saved_date_from, saved_date_to);

        });



function showLeadsData(period,from,to){
    $.ajax({
        url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
        type: "GET",
        cache:"false",
        data:{act:'get_leads_data',period:period,from:from,to:to},
        dataType: 'json',
        success: function(data){
               $("#total_leads").text(data.total_leads);
               $("#total_calls").text(data.total_calls);
               $("#total_emails").text(data.total_emails);
               $("#total_lifetime_leads").text(data.total_leads_lifetime);
               $("#total_lifetime_calls").text(data.total_calls_lifetime);
               $("#total_lifetime_emails").text(data.total_emails_lifetime);
        }
    });
}

//function showLeadsDataCustom(_from,_to){
//    $.ajax({
//        url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
//        type: "GET",
//        cache:"false",
//        data:{act:'get_leads_data',period:'custom',from:_from,to:_to},
//        dataType: 'json',
//        success: function(data){
//                //console.log(data.calls_query);
//               //console.log(data.email_query);
//               console.log(data);
//               $("#total_leads").text(data.total_leads);
//               $("#total_calls").text(data.total_calls);
//               $("#total_emails").text(data.total_emails);
//               $("#total_lifetime_leads").text(data.total_leads_lifetime);
//               $("#total_lifetime_calls").text(data.total_calls_lifetime);
//               $("#total_lifetime_emails").text(data.total_emails_lifetime);
//        }
//    });
//}

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
        }
    });
}


//function prepareLeadsChartCustom(_from,_to){
//    $.ajax({
//        url: "<?php echo SURL ?>ajaxresponse/ajax2.php",
//        type: "GET",
//        cache:"false",
//        data:{act:'get_leads_chart_data',period:'custom',from:_from,to:_to},
//        dataType: 'json',
//        success: function(_data){
//               
//            leadsChart.setData(_data);
//        }
//    });
//}


function getRandomInt(min, max) 
{
	return Math.floor(Math.random() * (max - min + 1)) + min;
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
  <form>
    <div class="col-sm-4 fr">
<!--      <div class="daterange daterange-inline add-ranges" data-format="MMMM D, YYYY" data-start-date="September 16, 2014" data-end-date="<?php echo date('F d, Y', strtotime(date('Y-m-d')));?>"> <i class="entypo-calendar"></i> <span>September 22, 2014 - <?php echo date('F d, Y', strtotime(date('Y-m-d')));?></span> </div>-->
        <div class="daterange daterange-inline add-ranges" data-format="MMMM D, YYYY" data-start-date="<?php echo $LM_PERIOD_FROM;?>" data-end-date="<?php echo $LM_PERIOD_TO;?>"> <i class="entypo-calendar"></i> <span><?php echo $LM_PERIOD_FROM;?> - <?php echo $LM_PERIOD_TO;?></span> </div>
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
          <div class="num" data-start="0" data-end="" data-postfix="" data-duration="1500" data-delay="600" id="total_calls">0</div>
          <h3>Calls</h3>
          <p>Lifetime Calls: <span id="total_lifetime_calls"></span></p>
        </div>
        </a> </div>
      <div class="col-sm-4"> <a href="admin.php?act=manageemails">
        <div class="tile-stats tile-aqua">
          <div class="icon"><i class="entypo-mail"></i></div>
          <div class="num" data-start="0" data-end="" data-postfix="" data-duration="1500" data-delay="1200" id="total_emails">0</div>
          <h3>Emails</h3>
          <p>Lifetime Emails: <span id="total_lifetime_emails"></span></p>
        </div>
        </a> </div>
    </div>
    <br />
    
    <br />
    <div class="row">
      <div class="col-sm-12">
        <div class="panel panel-primary" id="charts_env">
          <div class="panel-heading">
            <div class="panel-title">Leads History</div>
            <div class="panel-options" style="display:none;">
              <ul class="nav nav-tabs">
                <li class="active"><a href="#area-chart" data-toggle="tab">Area Chart</a></li>
                <li class=""><a href="#line-chart" data-toggle="tab">Line Charts</a></li>
                <li class=""><a href="#pie-chart" data-toggle="tab">Pie Chart</a></li>
              </ul>
            </div>
          </div>
          <div class="panel-body">
            <div class="tab-content">
            <div id="showGraph">
              <div class="tab-pane active" id="area-chart">
<!--                <div id="area-chart-demo" class="morrischart" style="height: 300px"></div>-->
                <div id="leads-chart" class="morrischart" style="height: 300px"></div>
              </div>
              </div>
              <div class="tab-pane" id="line-chart" style="display:none;">
                <div id="line-chart-demo" class="morrischart" style="height: 300px"></div>
              </div>
              <div class="tab-pane" id="pie-chart" style="display:none;">
                <div id="donut-chart-demo" class="morrischart" style="height: 300px;"></div>
              </div>

            </div>
          </div>
          <table class="table table-bordered table-responsive">
            <thead>
              <tr>
                <th width="50%" class="col-padding-1"> <div class="pull-left">
                    <div class="h4 no-margin"></div>
                    <small></small> </div>
                  <span class="pull-right pageviews"></span> </th>
                <th width="50%" class="col-padding-1"> <div class="pull-left">
                    <div class="h4 no-margin"></div>
                    <small></small> </div>
                  <span class="pull-right uniquevisitors"></span> </th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    