<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script>
        //var $j = jQuery;
</script>
    
<script type="text/javascript">
    
    jQuery(document).ready(function(){
        //var is_h = jQuery.isFunction(  );
        //console.log(is_h);
        //jQuery.modal();
        jQuery("#modal-share-message").hide();
    });
    
    function shareUs(formID){
        
        //var $ = jQuery;
        
        var data = $('#'+formID).serialize();
        console.log(data);
        
        jQuery.ajax({
            url:"<?php echo SURL ?>ajaxresponse/ajax2.php?act=share",
            type:"POST",
            cache:false,
            data:data,
            //data:{'to':$('#share-to').val(),'message':$('#share-message').val(),'share_report_type':$('#share_report_type').val(),'share_report_period':$('#share_report_period').val(),'share_report_period_to':$('#share_report_period_to'),'share_report_period_from':$('#share_report_period_from')},
            success: function(data){
                var res = jQuery.parseJSON(data);
                $('#modal-share-message').removeData();
                if(res.type=='error'){
                    $("#modal-share-message").removeClass("alert-success").addClass("alert-danger");
                    $("#modal-share-message").html(res.msg);
                    $("#modal-share-message").show();
                } else if(res.type=='success'){
                    //$("#modal-recommend-message2").show();
                    //$("#modal-recommend-message2").html(res.msg);
                    $("#modal-share-message").removeClass("alert-danger").addClass("alert-success");
                    $("#modal-share-message").html(res.msg);
                    $("#modal-share-message").show();
                }
            }
        });
        return false;
    }
    
</script>
    
    

<div class="row">
  <div class="viral-links">
    <h1>We Are Sure That You're Happy With Our Service</h1>
    <script>
        //var $j = jQuery;
    </script>
    <a href="javascript:;" onClick="$('#modal-recommend').modal('show',{backdrop:false});" class="green-btn"><i class="fa fa-thumbs-up"></i>Recommend Us</a>
    <a href="javascript:;" onclick="$('#modal-share-report').modal('show', {backdrop: 'static'});" class="blue-btn"><i class="fa fa-share-square"></i>Share This Report</a>
    </div>
</div>
<br />

<!---
Recommend Us Modal Dialog
-->

<div class="modal fade" id="modal-recommend">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Recommend Us</h4>
      </div>
      <div class="modal-body">
	    <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo  urlencode("http://localmedia.ae/seo-services/converstion-rate-optimization-cro/"); ?>" class="share-button fb"><i class="fa fa-facebook-square fl"></i>SHARE ON FACEBOOK</a>
	    <a target="_blank" href="https://twitter.com/intent/tweet?text=<?php echo  urlencode("Local Media: Converstion Rate Optimization (CRO)"); ?>&url=<?php echo  urlencode("http://localmedia.ae/seo-services/converstion-rate-optimization-cro/"); ?>" class="share-button tw"><i class="fa fa-twitter-square fl"></i>SHARE ON TWITTER</a>
	    <a target="_blank" href="http://www.linkedin.com/shareArticle?url=<?php echo  urlencode("http://localmedia.ae/seo-services/converstion-rate-optimization-cro/"); ?>&title=<?php echo  urlencode("Local Media: Converstion Rate Optimization (CRO)"); ?>&summary=<?php echo  urlencode("Local Media: Converstion Rate Optimization (CRO)"); ?>" class="share-button linkedin"><i class="fa fa-linkedin-square fl"></i>SHARE ON LINKEDIN</a>
	    <a onClick="jQuery('#emails_lst').show();" href="javascript:;" class="share-button email-share"><i class="fa fa-envelope fl"></i>SHARE ON EMAIL</a> </div>
	    <div class="form-group" id="emails_lst" style="display:none; width:60%; margin:0 auto;">
			<label class="">List of emails separated by comma (,): </label>
			<input type="text" name="email_lst" id="email_lst" value="" class="form-control"> <br>
			<p class="form-login-error" id="invalidEmailsError" style="display:none;">Please enter valid email addresses.</p>
			<p class="form-login-error" id="successEmailShare" style="display:none;">Email sent successfully.</p>
			<input class="btn btn-default" type="button" onClick="shareOnEmail();" value="Send Email" name="submit_send_email">
	    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<!---
share report Modal Dialog
-->
        
    <div class="modal fade share-modal" id="modal-share-report">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Share this report</h4>
      </div>
        
          <form id="share-us-form">
      <div class="modal-body">
              <input type="hidden" name="share_report_type" id="share_report_type" value="calls">
              <input type="hidden" name="share_report_period" id="share_report_period" value="lifetime">
              <input type="hidden" name="share_report_period_to" id="share_report_type" value="">
              <input type="hidden" name="share_report_period_from" id="share_report_period" value="">
          <div id="modal-share-message" class="alert alert-danger" role="alert"></div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="field-1" class="control-label">Share With :</label>
              <input type="text" class="form-control" id="share-to" name="to" placeholder="johndoe@example.com">
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label for="field-1"class="control-label">Message</label>
              <textarea class="form-control" id="share-message" name="message"  placeholder="Type Your Message Here..."></textarea>
            </div>
          </div>
        </div>
      </div>
         </form> 
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-info" onClick="shareUs('share-us-form');">Send Report</button>
      </div>
    </div>
  </div>
</div>
  