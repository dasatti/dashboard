<?php
include_once('file_include.php');
if(isset($_POST['mode'])){ 
/********************************        START - Edit Login Credential              *********************************/
  
 
 if($_POST['mode']=='send' && $_POST['act']=='loginprofile'){
	
		$sql = "UPDATE ".$tblprefix."admin SET username = '".addslashes($_POST['username'])."', password = '".addslashes($_POST['password'])."' WHERE id = '1'";
		$rs = $db->Execute($sql);
		
		if($rs){
		
			$msg=base64_encode("Login Credential updated successfully.");
			header("Location: admin.php?okmsg=$msg&act=".$_POST['act']);
			
		}else{
		
			$msg=base64_encode("Login Credential could not be updated");
			header("Location: admin.php?errmsg=$msg&act=".$_POST['act']);
			
		}
		exit;
	}
  
/********************************        END - Edit Login Credential              *********************************/
  
  
  
  
/********************************        START - Admin Profile Changes             *********************************/
  
  if($_POST['mode']=='send' && $_POST['act']=='profile'){
  
		 $sql = "UPDATE ".$tblprefix."admin SET
											name = '".addslashes($_POST['name'])."',
											email = '".addslashes($_POST['email'])."',
											noreplyemail = '".addslashes($_POST['noreplyemail'])."',
											notifyemail = '".addslashes($_POST['notifyemail'])."',
											username = '".addslashes($_POST['username'])."',
											paypal_email = '".addslashes($_POST['paypal_email'])."',
											email_status = '".addslashes($_POST['email_status'])."'
											WHERE id = '1'";
		
		$rs = $db->Execute($sql);
		if($rs){
		
			$_SESSION['interior_auth']['name']=$_POST['name'];
			$msg=base64_encode("Profile updated successfully.");
			header("Location: admin.php?okmsg=$msg&act=".$_POST['act']);
			
		}else{
		
			$msg=base64_encode("Profile could not be updated");
			header("Location: admin.php?errmsg=$msg&act=".$_POST['act']);
			
		}
		exit;
	}
  
  
/********************************        END- Admin Profile Changes             *********************************/
 
 
 
/********************************       START- Manage Content Managment            *********************************/
	/* Add Content Page */
 	if($_POST['mode']=='send' && $_POST['act'] == 'addcontentpage' && isset($_POST['addcontentSbt'])){ 
			$page_title = addslashes(trim($_POST['page_title']));
			$meta_title = addslashes(trim($_POST['meta_title']));
			$meta_keyword = addslashes(trim($_POST['meta_keyword']));
			$meta_phrase = addslashes(trim($_POST['meta_phrase']));
			$meta_description = addslashes(trim($_POST['meta_description']));
			$description = addslashes(trim($_POST['description'])); 
			
			$qry_max = "select MAX(porder) as maxid FROM ".$tblprefix."pagecontent";
			$rs_result = $db->Execute($qry_max);
	
			$nextOrder=$rs_result->fields['maxid']+1;
				
			$qry_insert = "INSERT INTO ".$tblprefix."pagecontent SET  page_title = '".$page_title."',meta_title = '".$meta_title."' , meta_keyword = '".$meta_keyword."' , meta_phrase = '".$meta_phrase."' , meta_description = '".$meta_description."' , description = '".addslashes($description)."', porder = $nextOrder, status = 1";		
			$rs_insert = $db->Execute($qry_insert);
	
			if($rs_insert){
				$okmsg = base64_encode('Page added successfully.');
				header("Location: admin.php?act=managecontentpages&okmsg=$okmsg");
				exit;	
			
			}
		}
 	
 
 	/* Edit Content Page */
if($_POST['mode']=='send' && $_POST['act'] == 'editcontentpage' && isset($_POST['editContentSbt'])){ 
			
			
			
			$page_title = addslashes(trim($_POST['page_title']));
			$meta_title = addslashes(trim($_POST['meta_title']));
			$meta_keyword = addslashes(trim($_POST['meta_keyword']));
			$meta_phrase = addslashes(trim($_POST['meta_phrase']));
			$meta_description = addslashes(trim($_POST['meta_description']));
			$description = addslashes(trim($_POST['description'])); 
			$eid=$_POST['eid'];			
										
			 $qry_insert = "UPDATE ".$tblprefix."pagecontent SET  page_title = '".$page_title."',meta_title = '".$meta_title."' , meta_keyword = '".$meta_keyword."' , meta_phrase = '".$meta_phrase."' , meta_description = '".$meta_description."' , description = '".addslashes($description)."' WHERE id=$eid";		
			$rs_insert = $db->Execute($qry_insert);
	
			if($rs_insert){
				$okmsg = base64_encode('Contents Updated successfully.');
				header("Location: admin.php?act=managecontentpages&okmsg=$okmsg");
				exit;	
			
			}
		}
		

	
/********************************       END- Manage Content Managment             *********************************/




	} // end of if(isset($_POST['mode']))
	
	


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



if(isset($_GET['mode'])){
	
	
	/* **************************************************** START:  Content Managment Section ****************************************/
	
	 /*START: Change Page Status */
		if($_GET['mode']=='updatepagestatus' && isset($_GET['status'])){

				$oldstatus = $_GET['status'];
				$act=$_GET['act'];
				$eid=$_GET['id'];
				$pid=$_GET['pid'];
				
				if($oldstatus == '0'){
					
					$newstatus = 1;
				}else{
		
					$newstatus = 0;
				}
				
				$update_qry = "UPDATE ".$tblprefix."pagecontent SET status = '".$newstatus."' WHERE id = '".$eid."'";
				$update_rs = $db->Execute($update_qry);
				
				if($update_rs){
				   
					$_SESSION['updatestatus']='yes'; 
					$okmsg = base64_encode('Page Status updated successfully.');
					
					if(isset($_GET['pid'])){
					
						header("Location: admin.php?act=$act&id=$pid&okmsg=$okmsg");
						exit;
					
					}else{
					
						header("Location: admin.php?act=$act&okmsg=$okmsg");
						exit;
					}
					
				
				}//end if($update_rs)
				exit;
		
		}/*END: Change Product Status */
		
		
		
		
		
	/* START: Delete Content Page   */
	
	if($_GET['mode']=='delcontentpage' && isset($_GET['delid'])){
	
		$did=$_GET['delid'];
		$act=$_GET['act'];	
		$pid=$_GET['pid'];
		
		
		// Delete Child pages
		$update_qry = "DELETE FROM ".$tblprefix."pagecontent WHERE parent_id=$did";
		$update_rs = $db->Execute($update_qry);
		
		
		
		// Delete parent page
		$update_qry = "DELETE FROM ".$tblprefix."pagecontent WHERE id=$did";
		$update_rs = $db->Execute($update_qry);
		
		if($update_rs){
		
			$okmsg = base64_encode('Page Deleted successfully.');
			
			
			if(isset($_GET['pid'])){
						
					header("Location: admin.php?act=$act&id=$pid&okmsg=$okmsg");
					exit;
			
				}else{
			
					header("Location: admin.php?act=$act&okmsg=$okmsg");
					exit;
				}
						
			
			}//end if($update_rs)
				
		}
	
	/* END: Delete Content Page  */
	
	
	
	
	
	
	
	/* START: Change Page displaying order */
	
	//if($_GET['mode']=='orderchange' && isset($_GET['status'])){
//			
//			$id=$_GET['id'];
//			$movetype=$_GET['status'];
//			$act=$_GET['act'];
//			
//			$qry_order = "select id,porder FROM ".$tblprefix."pagecontent WHERE id=".$id."";
//			$rs_result = $db->Execute($qry_order);
//			
//			
//			if($movetype=='up'){
//			
//			$neworder=$rs_result->fields['porder']-1;
//			$neworder2=$rs_result->fields['porder'];
//			$qry_order = "select id FROM ".$tblprefix."pagecontent WHERE porder=".$neworder;
//			$rs_result = $db->Execute($qry_order);
//			
//			$newid2=$rs_result->fields['id'];
//			
//			
//			}
//			
//			
//			if($movetype=='down'){
//			
//			
//			$neworder=$rs_result->fields['porder']+1;
//			$neworder2=$rs_result->fields['porder'];
//			
//			$qry_order = "select id FROM ".$tblprefix."pagecontent WHERE porder=".$neworder;
//			$rs_result = $db->Execute($qry_order);
//			
//			$newid2=$rs_result->fields['id'];
//			
//			}
//			
//			$qry_attrib="UPDATE ".$tblprefix."pagecontent SET porder='$neworder2' WHERE id=".$newid2;
//			$rs_attrib=$db->Execute($qry_attrib);
//			//echo "<br>";
//		    $qry_attrib="UPDATE ".$tblprefix."pagecontent SET porder='$neworder' WHERE  id=".$id;
//			$rs_attrib=$db->Execute($qry_attrib);
//			//exit;
//			if($rs_attrib){
//			
//			$okmsg = base64_encode("Page Displaying Order Changed Successfully. !");
//			header("Location: admin.php?act=$act&okmsg=$okmsg");
//			exit;
//			
//			}
//			
//			
//		
//		}
		
	/* END: Chnage Page displaying Order */
	
	/* START: Change category displaying order */
	
	if($_GET['mode']=='orderchange' && isset($_GET['status'])){
			
			$id=$_GET['id'];
			$movetype=$_GET['status'];
			$act=$_GET['act'];
			
			$qry_order = "select id,porder FROM ".$tblprefix."category WHERE id=".$id."";
			$rs_result = $db->Execute($qry_order);
			
			
			if($movetype=='up'){
			
			$neworder=$rs_result->fields['porder']-1;
			$neworder2=$rs_result->fields['porder'];
			
			$qry_order = "select id FROM ".$tblprefix."category WHERE porder=".$neworder;
			$rs_result = $db->Execute($qry_order);
			
			$newid2=$rs_result->fields['id'];
			
			
			}
			
			
			if($movetype=='down'){
			
			
			$neworder=$rs_result->fields['porder']+1;
			$neworder2=$rs_result->fields['porder'];
			
			$qry_order = "select id FROM ".$tblprefix."category WHERE porder=".$neworder;
			$rs_result = $db->Execute($qry_order);
			
			$newid2=$rs_result->fields['id'];
			
			}
			
			$qry_attrib="UPDATE ".$tblprefix."category SET porder='$neworder2' WHERE id=".$newid2;
			$rs_attrib=$db->Execute($qry_attrib);
			//echo "<br>";
		    $qry_attrib="UPDATE ".$tblprefix."category SET porder='$neworder' WHERE  id=".$id;
			$rs_attrib=$db->Execute($qry_attrib);
			//exit;
			if($rs_attrib){
			
			$okmsg = base64_encode("category Displaying Order Changed Successfully. !");
			header("Location: admin.php?act=$act&okmsg=$okmsg");
			exit;
			
			}
			
			
		
		}
	
	/* END: Chnage category displaying Order */
	
/* **************************************************** END:  Content Managment Section ****************************************/
		
	
	} // end  of if(isset($_GET['mode'])){

	 if(trim($_GET['act']) == "sendRequestCall"){
		session_start();
		//include_once("include/libmail.php");
		//echo $_GET['act'];
		$userName = $_SESSION['lm_auth']['name'];
		$userEmail = $_SESSION['lm_auth']['email'];
		$noreplyemail = $_SESSION['lm_auth']['noreplyemail'];
		//echo $userEmail."ffff";exit;
		$subject = "Call Request";
		$body = "Hi Admin,<br> ".$userName." has requested for call. Please take appropriate action.<br> Thanks,<br>Agency Dashboard";
	//echo $_GET['act'];
                $q = "SELECT name, notifyemail FROM tbl_admin WHERE account_type='super'"; 
                $r = $db->Execute($q);
                $t = $r->RecordCount();
                $su_emails = array();
                if($t>0){
                    while ($rsa = $r->FetchRow()) {
				    $su_emails[]=$rsa['notifyemail'];
				}
                }
                //echo $t;
			 //print_r($su_emails);
			 //echo $emails = implode(',', $su_emails);
		
		$mContact = new Mail;
		$mContact->From("Agency Dashboard <".$noreplyemail.">");
		$mContact->To($su_emails);
          //$mContact->To('progos.nabbas@gmail.com');
		$mContact->Subject($subject);
		$mContact->Body(stripslashes($body));
		$mContact->Priority(3) ; 
		$mContact->Send();
	}
 if(trim($_GET['act']) == "shareThisOnEmail"){
		$emlst = $_GET['emlst'];
		$userName = $_SESSION['lm_auth']['name'];
		$userEmail = $_SESSION['lm_auth']['email'];
		$noreplyemail = $_SESSION['lm_auth']['noreplyemail'];
		//echo $userEmail."ffff";exit;
		$details_lnk = '<a href="http://localmedia.ae/seo-services/converstion-rate-optimization-cro/">Read More...</a>';
		$subject = "Converstion Rate Optimization (CRO)";
		$body = 'Hi,<br>Please check this out<br><br>';
		$body .='<div class="two_third">
<h1>Converstion Rate Optimization (CRO)</h1>
<p>Conversion Optimization is the science and art of getting a higher percentage of your web visitors to take action and becoming a lead or customer.<br>
<strong>Science:</strong> you cannot simply “guess and hope” which changes to your web pages will achieve a higher conversion rate. Instead, Local Media’s experts develop and test valid hypotheses, run controlled tests, and evaluate results to lock in the improvement.<br>
<strong>Art:</strong> a statistician or engineer alone cannot create the visuals and messaging that engage your web visitors. Local Media’s organic and structured process creatively applies learning from thousands of test results to test only the best variations on your website.</p>
<h2>Generate More of what you need: More Leads or Ecommerce Sales</h2>
<p>Local Media provides services that deliver improved conversion rates for Ecommerce and lead-generation website marketers. Our clients enjoy conversion rate increases of between 10% to 290%!</p>
<h2>We’ll Do All of the Work for You</h2>
<p>As a Local Media client, you will get the full-service optimization solution for the best results from A/B/n split and Multivariate tests &ndash; including web analytics, design, copywriting, development and implementation. You get test results that statistically prove which website or landing page Variation gives you maximum conversions, you can significantly improve your conversion rate without adding extra work for your web or marketing teams.</p>
<h2>Why choose Local Media?</h2>
<p>For us, CRO is not just about identifying how users convert. It’s about understanding why they convert and, crucially, why they don’t. The ideal landing page can only be created with a clear view and an acute sense of user behavior.<br>
Our systematic approach primarily consists of user journey analysis, heat map analysis and attribution modelling to define the usability of your key pages. We then hypothesize, experiment, build and rebuild through A/B testing, competitor benchmarking and design and development consultation to optimize your landing pages and ultimately maximize your ROI.</p>
</div>'.$details_lnk;
		$body .='<br><br><br>Thanks,<br>'.$userName;
          $su_emails = explode(',',$emlst);
		
		$mContact = new Mail;
		$mContact->From($userName." <".$userEmail.">");
		$mContact->To($su_emails);
          //$mContact->To('progos.nabbas@gmail.com');
		$mContact->Subject($subject);
		$mContact->Body(stripslashes($body));
		$mContact->Priority(3) ; 
		$mContact->Send();
	}

?>