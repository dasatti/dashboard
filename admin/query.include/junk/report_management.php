<?php

######################
#
# 	POST SECTION
#
######################


//-----------ADD Report-----------

	if($_POST['mode'] == 'send' && $_POST['act'] == 'managereports' && isset($_POST['AddReportSbt'])){
	
			$buy_buy = $_POST['buy_buy'];
			$buy_target = addslashes(trim($_POST['buy_target']));
			$buy_stop = addslashes(trim($_POST['buy_stop']));
			$sell_buy = $_POST['sell_buy'];
			$sell_target = addslashes(trim($_POST['sell_target']));
			$sell_stop = addslashes(trim($_POST['sell_stop']));
			$report_date = date('Y-m-d');
			
			$sql_query = "INSERT INTO ".$tblprefix."reports SET
														buy_buy = '".$buy_buy."',
													    buy_target = '".$buy_target."',
														buy_stop = '".$buy_stop."',
														sell_buy = '".$sell_buy."',
														sell_target = '".$sell_target."',
														sell_stop = '".$sell_stop."',
														report_date = '".$report_date."'";
			$rs_cate = $db->Execute($sql_query);
		
			if($rs_cate){
				$okmsg = base64_encode("Report Added successfully.");
				header("Location: admin.php?okmsg=$okmsg&act=".$_POST['act']);
				exit;	 
			}// END:  if($rs_cate)
			
	   } // END: if($_POST['mode'] == 'send' && $_POST['act'] == 'managereports' && isset($_POST['AddReportSbt']))
	   
//-----------Update report-----------
	
	if($_POST['mode'] == 'send' && $_POST['act'] == 'managereport' && isset($_POST['UpdateRepSbt'])){
		
		$buy_buy = $_POST['buy_buy'];
		$buy_target = addslashes(trim($_POST['buy_target']));
		$buy_stop = addslashes(trim($_POST['buy_stop']));
		$sell_buy = $_POST['sell_buy'];
		$sell_target = addslashes(trim($_POST['sell_target']));
		$sell_stop = addslashes(trim($_POST['sell_stop']));
		
		
		$rid = $_POST['rid'];
		
		$sql_update = "UPDATE ".$tblprefix."reports SET 
													buy_buy = '".$buy_buy."',
													buy_target = '".$buy_target."',
													buy_stop = '".$buy_stop."',
													sell_buy = '".$sell_buy."',
													sell_target = '".$sell_target."',
													sell_stop = '".$sell_stop."'
													WHERE id = '".$rid."'";
	
		$rs = $db->Execute($sql_update);
		
		if($rs){
		 $okmsg = base64_encode("Report Updated successfully. !");
		 header("Location: admin.php?okmsg=$okmsg&act=".$_POST['act']);
		exit;	  
	       }
	}/* END: if($_POST['mode'] == 'send' && $_POST['act'] == 'managereport' && isset($_POST['UpdateRepSbt'])) */
	
//---------Multiple reports  Delete---------	   
    if($_POST['act']=='managereport' && $_POST['delete'] == 'Delete All' ){ 
							
			$delfaqid= array();
			$delfaqid = $_POST['checkbox'];
													
			if(count($delfaqid) > 0){
			
				foreach($delfaqid as $key => $value){
				
					$del_qry = " DELETE FROM ".$tblprefix."reports WHERE id = '".$rs_sql->fields['id']."' " ;
					$rs_del = $db->Execute($del_qry);
					$rs_sql->MoveNext();
					
				}//end foreach
			}//end if count
			
			$okmsg = base64_encode("selected reports deleted successfully.. !");
			header("Location: admin.php?okmsg=$okmsg&act=".$_POST['act']);
			exit;
							 
		} 
	/* END:  if($_POST['act']=='managereport' && $_POST['delete'] == 'Delete All' )  */	   
	   

######################
#
# 	GET SECTION
#
######################

//---------Single report Delete---------

	if($_GET['mode']=='delreport'){
		    
			$delid = base64_decode($_GET['rid']);
			$del_qry = "DELETE FROM ".$tblprefix."reports WHERE id = '".$delid."'" ;
			$rs_del = $db->Execute($del_qry);
			if($rs_del){
				$okmsg = base64_encode("Report deleted successfully.. !");
				header("Location: admin.php?okmsg=$okmsg&act=managereports");
				exit;
			}
	} /* if($_GET['mode']=='delreport') */
?>