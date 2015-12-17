<?php
set_time_limit(0);						  //	echo "You want to import prod Window";
ini_set("memory_limit","500M");


######################
#
# 	POST SECTION
#
######################

//-----------ADD Album Image-----------

	if($_POST['mode'] == 'send' && $_POST['act'] == 'addalbum' && isset($_POST['AddAlbumSbt'])){
	
		$title = safe_string($_POST['title']);
		
		$formats = array("jpeg", "gif", "jpg","png");
		$file_name_array 	= $_FILES['album_image'];
		$file_ext = ltrim(strtolower(strrchr($file_name_array['name'],'.')),'.');
		
		if(!in_array($file_ext,$formats)){
			$okmsg = base64_encode("<font color=#FF0000>Format is not allowed to upload .</font>");
		 	header("Location: admin.php?act=managealbums&okmsg=$okmsg");
			exit;
		}else{ 
		
		$file_temp 			= $_FILES['album_image']['tmp_name'];
		$filename 			=  date('ymdghs').$_FILES['album_image']['name']; 
		$filename_small		=	'tmb-'.$filename;
		
		
			
		$dir = ROOT.ALBUM_FOLDER;
		$filename_with_dir = $dir.$filename;

		@copy($file_temp,$filename_with_dir);
		@list($width, $height) = getimagesize($filename_with_dir);
		
		//@$thumb = PhpThumbFactory::create($filename_with_dir);
		//@$thumb->cropFromCenter(979, 252);
		//@$thumb->save($filename_with_dir, 'jpg');
				
		//cropImage(887, 174, $dir.$filename, 'png', $dir.$filename);
		cropImage(340, 250, $dir.$filename, 'jpg', $dir.$filename_small);

		$sql = "INSERT INTO ".$tblprefix."albums SET title = '".$title."',image='".$filename."'";
		$res = $db->Execute($sql); 
			
			if($res){
				//updatesliderXML();
		 		$okmsg = base64_encode("Album Information Added successfully. !");
		 		@header("Location: admin.php?okmsg=$okmsg&act=managealbums");
		 		exit;	  
			}
	      }
	   }
	   
//-------------Update Album Image ----------

	if($_POST['mode'] == 'send' && $_POST['act'] == 'managealbums' && isset($_POST['UpdateAlbumSbt'])){ 
		
		$title 		= safe_string($_POST['title']);
		$aid 		= $_POST['aid'];	
		
		if($_FILES['album_image']['name']!=''){
			
			$upd_qry = "SELECT * FROM ".$tblprefix."albums WHERE id = '".$aid."'";
		    $rs_upd = $db->Execute($upd_qry);
		
			if($rs_upd->fields['image']!=""){
								
				$a_img_del = ROOT.ALBUM_FOLDER.$rs_upd->fields['image'];
				
				@unlink($a_img_del);
				@unlink(ROOT.ALBUM_FOLDER.'tmb-'.$rs_upd->fields['image']);
			}
			
			$file_temp 			= $_FILES['album_image']['tmp_name'];
			$filename 			=  date('ymdghs').$_FILES['album_image']['name']; 
			$filename_small		=	'tmb-'.$filename;
	
			$dir = ROOT.ALBUM_FOLDER;
			$filename_with_dir = $dir.$filename;
	
			@copy($file_temp,$filename_with_dir);
			@list($width, $height) = getimagesize($filename_with_dir);
			
			cropImage(340, 250, $dir.$filename, 'jpg', $dir.$filename_small);
		}else{
			$filename = $_POST['hid_image'];
		}

		$sql_update = "UPDATE ".$tblprefix."albums SET 
												 title = '".$title."',
												 image = '".$filename."'
												 WHERE id = '".$aid."'";
		$rs = $db->Execute($sql_update);
			
		if($rs){
		 	$okmsg = base64_encode("Album Updated successfully.");
		 	header("Location: admin.php?okmsg=$okmsg&act=".$_POST['act']);
			exit;	  
	    }
	}
		
//---------Single album Image  Delete---------

	if($_GET['mode']=='delalbum'){
		
		$recent_qry = "SELECT * FROM ".$tblprefix."albums WHERE id = '".base64_decode($_GET['aid'])."'";
		$rs_recent = $db->Execute($recent_qry);
		
		$a_img_del="";
		$a_img_del = ROOT.ALBUM_FOLDER.$rs_recent->fields['image'];
				
		if(file_exists($a_img_del)){
			@unlink($a_img_del);
			@unlink(ROOT.ALBUM_FOLDER.'tmb-'.$rs_recent->fields['image']);
		}
		
		$del_qry = " DELETE FROM ".$tblprefix."albums WHERE id = '".base64_decode($_GET['aid'])."'";
		$rs_review = $db->Execute($del_qry);
		
		//updatesliderXML();
		$okmsg = base64_encode("Album Deleted successfully. !");
		header("Location: admin.php?okmsg=$okmsg&act=".$_GET['act']);
		exit;
	} 
?>