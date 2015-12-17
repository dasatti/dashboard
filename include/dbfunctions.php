<?php include('siteconfig.inc.php');

//getting the avg of the rating 









											


//splitting string into two pieces
function splitStr($str){
		$arrStr 	=  array();
		$arrStr 	= explode(' ',$str);

		$strUpdated  = "";
		$count 		 = count($arrStr) - 1;
		for($i=0;$i<$count;$i++){
			if($i + 1< $count){
				$strUpdated .= $arrStr[$i]." ";
			}else{
				$strUpdated .= $arrStr[$i];
			}
		}
	return $strUpdated;
}



//safe string for select and insertion 
function safe_string($value){
	$value = strtolower($value);

	if (get_magic_quotes_gpc($value))
	{
		$value = stripslashes($value);
	}

	if (!is_numeric($value))
	{
		$value = "" . mysql_real_escape_string($value) . "";
	}
	return trim($value);
}//end safe_string



// Date displayed for users: like  1 Jan, 2005
function mysqltonormal($dated){
	$dated = explode('-', $dated);
	$YYYY  = $dated[0];
	$MM = $dated[1];
	$DD = $dated[2];
	$YY = substr($YYYY,2);
  	$final = date("d M, Y", mktime(0,0,0,$MM,$DD, $YYYY));
	//$final = $MM.'/'.$DD.'/'.$YYYY;
	return $final;		
}

function normaltomysql($dated){

	$dated = explode('/', $dated);
	$MM  = $dated[0];
	$DD = $dated[1];
	$YYYY = $dated[2];
	$final = $YYYY.'-'.$DD.'-'.$MM;
	if($final !='--') {
		return $final;		
	}
}


function getyear($dob){

	$birthday = str_replace('-','',$dob);
	$today = date('Ymd');
	return $age = (int)(($today - $birthday)/10000);
}
  
?>