<?php

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
		}else if(val=='Yesterday'){
			sortCriterean = 'yesterday';
//                        calculateROI(sortCriterean);
		}else if(val=='Last 7 Days'){
			sortCriterean = 'last_7_days';
//                        calculateROI(sortCriterean);			
		}else if(val=='Last 30 Days'){
			sortCriterean = 'last_30_days';
//                        calculateROI(sortCriterean);
		
		}else if(val=='This Month'){
			sortCriterean = 'this_month';
//                        calculateROI(sortCriterean);			
		}else if(val=='Last Month'){	
			sortCriterean = 'last_month';
//                        calculateROI(sortCriterean);
		}else if(val=='Lifetime'){
			sortCriterean = 'lifetime';
//                        calculateROI(sortCriterean);
		}else{
			var first = document.getElementById("daterangepicker_start").value;
			var secon = document.getElementById("daterangepicker_end").value;
			sortCriterean = first+'#'+secon;
//                        calculateROI(sortCriterean);			
		}
                
                var date_from = document.getElementById("daterangepicker_start").value;
                var date_to = document.getElementById("daterangepicker_end").value;
                calculateROI(sortCriterean,date_from,date_to);
                
                saveDateRange(sortCriterean,date_from,date_to);
				
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


<!--<script>
        var $j = jQuery;
    </script>
<div class="row">
  <div class="viral-links">
    <h1>We Are Sure That You're Happy With Our Service</h1>
    <a href="javascript:;" onClick="$j('#modal-recommend').modal('show',{backdrop:false});" class="green-btn"><i class="fa fa-thumbs-up"></i>Recommend Us</a></div>
</div>-->

<?php include("bodies/recommend.php"); ?>
