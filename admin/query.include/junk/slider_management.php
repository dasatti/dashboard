<?php
set_time_limit(0);						  //	echo "You want to import prod Window";
ini_set("memory_limit","500M");


######################
#
# 	POST SECTION
#
######################

//-----------ADD Slider Image-----------

	if($_POST['mode'] == 'send' && $_POST['act'] == 'addslider' && isset($_POST['AddSliderSbt'])){
	
		$title = safe_string($_POST['title']);
		
		$formats = array("jpeg", "gif", "jpg","png");
		$file_name_array 	= $_FILES['slider_image'];
		$file_ext = ltrim(strtolower(strrchr($file_name_array['name'],'.')),'.');
		
		if(!in_array($file_ext,$formats)){
			$okmsg = base64_encode("<font color=#FF0000>Format is not allowed to upload .</font>");
		 	header("Location: admin.php?act=manageslider&okmsg=$okmsg");
			exit;
		}else{
			
		$sql_query = "INSERT INTO ".$tblprefix."slider SET
															title = '".$title."'";
		$rs_cate = $db->Execute($sql_query);
		
		$recent_id =  mysql_insert_id();  
		
		$file_temp 			= $_FILES['slider_image']['tmp_name'];
		$filename 			=  $recent_id.'.jpg'; 
		$filename_small		=	'small'.$filename;
		
			
		$dir = ROOT.SLIDER_FOLDER;
		$filename_with_dir = $dir.$filename;

		@copy($file_temp,$filename_with_dir);
		@list($width, $height) = getimagesize($filename_with_dir);
		
		/*$thumb = PhpThumbFactory::create($filename_with_dir);
		$thumb->cropFromCenter(979, 252);
		$thumb->save($filename_with_dir, 'jpg');*/
					
		//cropImage(887, 174, $dir.$filename, 'png', $dir.$filename);
		//cropImage(80, 50, $dir.$filename, 'jpg', $dir.$filename_small);
		
		$sql_update 	= "update ".$tblprefix."slider set image='".$filename."'  where id = '".$recent_id."'";
		$rs_update		= $db->Execute($sql_update); 
			
			if($rs_update){
				//updatesliderXML();
		 		$okmsg = base64_encode("Slider Information Added successfully. !");
		 		@header("Location: admin.php?okmsg=$okmsg&act=manageslider");
		 		exit;	  
			}
	      }
	   }
	   
//-------------Update Slider Image ----------

	if($_POST['mode'] == 'send' && $_POST['act'] == 'manageslider' && isset($_POST['UpdatesliderSbt'])){ 
		
		$title 		= safe_string($_POST['title']);
		
		$sid 		= $_POST['sid'];
			
		$upd_qry = "SELECT * FROM ".$tblprefix."slider WHERE id = '".$sid."'";
		$rs_upd = $db->Execute($upd_qry);
			
		$sql_update = "UPDATE ".$tblprefix."slider SET 
														title 		= '".$title."'
														WHERE id 	= '".$sid."'";
		$rs = $db->Execute($sql_update);
		
		$formats = array("jpeg", "gif", "jpg" , "png");
		
		$file_name 			= $_FILES['slider_img']['name'];
		$file_temp 			= $_FILES['slider_img']['tmp_name'];
		$filename 			=  $sid.'.jpg'; 
		$filename_small		=	'small'.$filename;
		$dir 				= ROOT.SLIDER_FOLDER;
		
		if(!empty($file_name)){
			
			if($rs_upd->fields['image']!=""){					
				if(file_exists($dir."/".$rs_upd->fields['image'])){
					@unlink($dir."/".$rs_upd->fields['image']);
				}	
			}
			
			$filename_with_dir = $dir.$filename;
			$filename_with_thumbdir = $dir.$filename_new_thumb;
			$filename_with_zoomthumbdir = $dir.$filename_zoom_thumb;
			
			@copy($file_temp,$filename_with_dir);
			@list($width, $height) = getimagesize($filename_with_dir);
					
			//cropImage(887, 174, $file_temp, 'png', $dir.$filename);
			//cropImage(80, 50, $dir.$filename, 'jpg', $dir.$filename_small);
			
			/*$thumb = PhpThumbFactory::create($filename_with_dir);
			$thumb->cropFromCenter(979, 252);
			$thumb->save($filename_with_dir, 'jpg');887,174*/

			
			$sql_update = "UPDATE ".$tblprefix."slider SET 
														  image = '".$filename."'
														  WHERE id = '".$sid."'";
			$rs = $db->Execute($sql_update);
			
			if($rs){
				//updatesliderXML();
		 		$okmsg = base64_encode("Slider Information Updated successfully.");
		 		header("Location: admin.php?okmsg=$okmsg&act=".$_POST['act']);
				exit;	  
	       	}
			
		   }
		}
		
//---------Single Slider Image  Delete---------

	if($_GET['mode']=='delslider'){
		
		$recent_qry = "SELECT * FROM ".$tblprefix."slider WHERE id = '".base64_decode($_GET['sid'])."'";
		$rs_recent = $db->Execute($recent_qry);
		
		$a_img_del="";
		$a_img_del = ROOT.SLIDER_FOLDER.$rs_recent->fields['image'];
				
		if(file_exists($a_img_del)){
			@unlink($a_img_del);
			@unlink($t_img_del);
		}
		
		$del_qry = " DELETE FROM ".$tblprefix."slider WHERE id = '".base64_decode($_GET['sid'])."' " ;
		$rs_review = $db->Execute($del_qry);
		
		//updatesliderXML();
		$okmsg = base64_encode("Slider Image Deleted successfully. !");
		header("Location: admin.php?okmsg=$okmsg&act=".$_GET['act']);
		exit;
	} 
?>