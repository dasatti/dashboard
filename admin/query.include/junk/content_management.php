<?php if($_POST['mode']=='send' && $_POST['act']=='contentpage'){
		
		$page_title = addslashes($_POST['page_title']);
		$meta_title = addslashes($_POST['meta_title']);
		$meta_keyword = addslashes($_POST['meta_keyword']);
		$meta_phrase = addslashes($_POST['meta_phrase']);
		$meta_description = addslashes($_POST['meta_description']);
		$description = addslashes($_POST['description']);
		$page_type = $_POST['page_type'];
		
		$pagename = str_replace('_',' ',$page_type);
		$pagename = $pagename;
		
		$qry_update = "UPDATE ".$tblprefix."pagecontent SET 
														 page_title = '".$page_title."', 
														 meta_title = '".$meta_title."', 
														 meta_keyword = '".$meta_keyword."', 
														 meta_phrase = '".$meta_phrase."', 
														 meta_description = '".$meta_description."', 
														 description = '".$description."' 
														 WHERE page_type = '".$page_type."'";
		$rs = $db->Execute($qry_update);

		if($rs){
		
			$_SESSION['sportal-adminauth']['name']=$_POST['name'];
			$msg=base64_encode("$pagename Contents Updated successfully.");
			header("Location: admin.php?okmsg=$msg&page=$page_type&act=".$_POST['act']);
			
		}else{
		
			$msg=base64_encode("Contents could not be updated");
			header("Location: admin.php?errmsg=$msg&act=".$_POST['act']);
			
		}//if($rs)
		exit;
	
	}
	/* END: if($_POST['mode']=='send' && $_POST['act']=='contentpage')*/
	
if($_POST['mode']=='send' && $_POST['act']=='managecontentpage'){
		
		$page_title = addslashes($_POST['page_title']);
		$meta_title = addslashes($_POST['meta_title']);
		$meta_keyword = addslashes($_POST['meta_keyword']);
		$meta_phrase = addslashes($_POST['meta_phrase']);
		$meta_description = $_POST['meta_description'];
		$description = $_POST['description'];
		$page_type = $_POST['page_type'];
		
		$pagename = str_replace('_',' ',$page_type);
		$pagename = $pagename;
		
		$qry_update = "UPDATE ".$tblprefix."pagecontent SET 
														 page_title = '".$page_title."', 
														 meta_title = '".$meta_title."', 
														 meta_keyword = '".$meta_keyword."', 
														 meta_phrase = '".$meta_phrase."', 
														 meta_description = '".$meta_description."', 
														 description = '".$description."' 
														 WHERE page_type = '".$page_type."'";
		$rs = $db->Execute($qry_update);

		if($rs){
		
			$_SESSION['maths-adminauth']['name']=$_POST['name'];
			$msg=base64_encode("$pagename Contents Updated successfully.");
			header("Location: admin.php?okmsg=$msg&page=$page_type&act=".$_POST['act']);
			
		}else{
		
			$msg=base64_encode("Contents could not be updated");
			header("Location: admin.php?errmsg=$msg&act=".$_POST['act']);
			
		}//if($rs)
		exit;
	
	}/* END: if($_POST['mode']=='send' && $_POST['act']=='contentpage')*/
	
?>