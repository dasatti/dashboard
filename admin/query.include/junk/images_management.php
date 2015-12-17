<?php
set_time_limit(0);						  //	echo "You want to import prod Window";
ini_set("memory_limit","500M");


######################
#
# 	POST SECTION
#
######################

//-----------ADD Album Image-----------

	if($_POST['mode'] == 'send' && $_POST['act'] == 'addimages' && isset($_POST['AddImagesSbt'])){
	
		$title = safe_string($_POST['title']);
		
		$formats = array("jpeg", "gif", "jpg","png");
		$file_name_array 	= $_FILES['image'];
		$file_ext = ltrim(strtolower(strrchr($file_name_array['name'],'.')),'.');
		
		if(!in_array($file_ext,$formats)){
			$okmsg = base64_encode("<font color=#FF0000>Format is not allowed to upload .</font>");
		 	header("Location: admin.php?act=manageimages&aid=".base64_encode($_POST['aid'])."&okmsg=$okmsg");
			exit;
		}else{ 
		
			$file_temp 			= $_FILES['image']['tmp_name'];
			$filename 			=  $_POST['aid']."_".date('ymdghs').$_FILES['image']['name']; 
			$filename_small		=	'tmb-'.$filename;
			
			$dir = ROOT.IMAGES_FOLDER;
			$filename_with_dir = $dir.$filename;

			@copy($file_temp,$filename_with_dir);
			@list($width, $height) = getimagesize($filename_with_dir);
		
		
			cropImage(340, 250, $dir.$filename, 'jpg', $dir.$filename_small);

			$sql = "INSERT INTO ".$tblprefix."albums_images SET title = '".$title."',image='".$filename."',aid='".$_POST['aid']."'";
			$res = $db->Execute($sql); 
			
			if($res){
				//updatesliderXML();
		 		$okmsg = base64_encode("Image Added successfully. !");
		 		@header("Location: admin.php?okmsg=$okmsg&act=albumimages&aid=".base64_encode($_POST['aid'])."");
		 		exit;	  
			}
	      }
	   }
	   
//-------------Update Image ----------

	if($_POST['mode'] == 'send' && $_POST['act'] == 'albumimages' && isset($_POST['UpdateImageSbt'])){ 
		
		$title 		= safe_string($_POST['title']);
		$aid 		= $_POST['aid'];
		$id 		= $_POST['id'];	
		
		if($_FILES['image']['name']!=''){
			
			$upd_qry = "SELECT * FROM ".$tblprefix."albums_images WHERE id = '".$id."'";
		    $rs_upd = $db->Execute($upd_qry);
		
			if($rs_upd->fields['image']!=""){
								
				$a_img_del = ROOT.IMAGES_FOLDER.$rs_upd->fields['image'];
				
				@unlink($a_img_del);
				@unlink(ROOT.IMAGES_FOLDER.'tmb-'.$rs_upd->fields['image']);
			}
			
			$file_temp 			= $_FILES['image']['tmp_name'];
			$filename 			=  $aid."_".date('ymdghs').$_FILES['image']['name']; 
			$filename_small		=	'tmb-'.$filename;
	
			$dir = ROOT.IMAGES_FOLDER;
			$filename_with_dir = $dir.$filename;
	
			@copy($file_temp,$filename_with_dir);
			@list($width, $height) = getimagesize($filename_with_dir);
			
			cropImage(340, 250, $dir.$filename, 'jpg', $dir.$filename_small);
		}else{
			$filename = $_POST['hid_image'];
		}

		$sql_update = "UPDATE ".$tblprefix."albums_images SET 
												 		title = '".$title."',
												 		image = '".$filename."'
												 		WHERE id = '".$id."'";
		$rs = $db->Execute($sql_update);
			
		if($rs){
		 	$okmsg = base64_encode("Image Updated successfully.");
		 	header("Location: admin.php?okmsg=$okmsg&act=".$_POST['act']."&aid=".base64_encode($aid));
			exit;	  
	    }
	}
		
//---------Single Image  Delete---------

	if($_GET['mode']=='delimage'){
		
		$recent_qry = "SELECT * FROM ".$tblprefix."albums_images WHERE id = '".base64_decode($_GET['id'])."'";
		$rs_recent = $db->Execute($recent_qry);
		
		$a_img_del="";
		$a_img_del = ROOT.ALBUM_FOLDER.$rs_recent->fields['image'];
				
		if(file_exists($a_img_del)){
			@unlink($a_img_del);
			@unlink(ROOT.IMAGES_FOLDER.'tmb-'.$rs_recent->fields['image']);
		}
		
		$del_qry = " DELETE FROM ".$tblprefix."albums_images WHERE id = '".base64_decode($_GET['id'])."'";
		$rs_review = $db->Execute($del_qry);
		
		//updatesliderXML();
		$okmsg = base64_encode("Image Deleted successfully. !");
		header("Location: admin.php?okmsg=$okmsg&act=".$_GET['act']."&aid=".$_GET['aid']);
		exit;
	} 
?>