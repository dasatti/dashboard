<?php 
include_once('file_include.php');
include('process.php');
	



require_once dirname(__FILE__).'/classes/dashboard/DashboardCommon.php';

$saved_dates = DashboardCommon::getSavedDateRangeFilter();
$LM_PERIOD = $saved_dates['period'];
$LM_PERIOD_FROM = date('F d, Y', strtotime($saved_dates['from']));
$LM_PERIOD_TO = date('F d, Y', strtotime($saved_dates['to']));


######################
#
# 	POST SECTION
#
######################

if(isset($_POST['mode'])){

	$inner_page = $_POST['request_page'];
	
	if($inner_page==""){
		$inner_page = "index";
	}
	
	include("query.include/".$inner_page.".php");

}//end if(isset($_POST['mode']))

######################
#
# 	GET SECTION
#
######################

if(isset($_GET['mode'])){
	
	$inner_page = $_GET['request_page'];
	
	if($inner_page==""){
		$inner_page = "index";
	}

	include("query.include/".$inner_page.".php");
		
}// END if(isset($_GET['mode']))
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="Admin Panel" />
<meta name="author" content="" />
<title><?php echo ADMIN_TITLE?></title>
<?php include('script_include.php');?>
</head>
<body class="page-body  page-fade" data-url="http://neon.dev">
<div class="page-container">
  <!-- add class "sidebar-collapsed" to close sidebar by default, "chat-visible" to make chat appear always -->
  <?php
  if($_SESSION['lm_auth']['account_type']=='super'){
        define('SU',1);
        define('SU_PRE','su');
  	include("include/leftpanel_admin.php");
  }else{
	include("include/leftpanel_client.php");  
  }
  ?>
  <div class="main-content">
    <?php
	  include("include/header.php"); 
	  
            $dir = (defined('SU'))?'su_bodies/':'bodies/';
            
                if(isset($_REQUEST['act'])){
                   
                        $f = $_REQUEST['act'].'.php';
                        //if(defined('SU')) $f = SU_PRE.$f;
                        
                        if(file_exists("$dir"."$f")){ include "$dir"."$f";}
                        
                        
//                        /echo 'here',$dir.$f; die();
                        
                        else include $dir.'/error.php';

                        /*if(file_exists("bodies/".$_REQUEST['act'].".php")){

                                include("bodies/".$_REQUEST['act'].".php");

                        }else{
                                include("bodies/error.php");

                        }*/
                }else{

                        include($dir."dashboard.php");
                }
			
		include("include/footer.php"); 
		?>
  </div>

 
  
  
<?php


    include('include/modals.php');
?>  


<script type="text/javascript">

	function sendRequestCall(){		
		$.ajax({	
		  url: "<?php echo SURL?>process.php",
		  type: "GET",
		  catche:"false",
		  data:{act:"sendRequestCall"},
		  success: function (data) {		  
			 //alert(data);
			  }
		})
	}
	
	function shareOnEmail(){
		var email_lst = jQuery('#email_lst').val();
		var email_lst_arr = email_lst.split(',');
		var proceed = true;
		for(var i=0; i < email_lst_arr.length; i++){
			if(!validateShareEmail(email_lst_arr[i].trim())){
				proceed = false;
				}
			}
		if(proceed){
			jQuery('#invalidEmailsError').hide();
			$.ajax({	
			  url: "<?php echo SURL?>process.php",
			  type: "GET",
			  catche:"false",
			  data:{act:"shareThisOnEmail",emlst:email_lst},
			  success: function (data) {		  
				 jQuery('#successEmailShare').show();
				  }
			});
			}else{
				jQuery('#invalidEmailsError').show();
				}
		}
function validateShareEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}
</script>

</div>


</body>
</html>