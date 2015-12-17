<?php 
ob_start();
session_start();

//echo '<pre>';
//print_r($_POST);
//exit;
/*include("common_files/logincheck.php");
include("../connection/connection.php");
include("../common_files/language.php");
$i=0;
foreach ($_POST["file_name"] as $fname)
{
 $file_name = $_POST["file_name_orig"][$i];
 $i++;
	$sqlquery = "insert into tbl_video (js_id,video_name,video_path) values('$js_id','$file_name','$fname')";
	$db->query($sqlquery);
	
}*/
echo 'Video uploaded successfully';
header("location:add_video.php?msg=1");
?>