<?php
/********************************************************
* File Name: sitefunction.php	                        *
*												      	*
* File Description: Common PHP functions defined here   *
*												        * 
* Created Date: 09-Oct-2014     					    *
* 													    *
* File Created By: Fahim Ullah Khattak                  *
* 												        *
* Modify Date: 09-Oct-2014                              *
*                       						        *
* Modified By: Fahim Ullah Khattak  					*
********************************************************/

function next_ten_years(){
	$cur = date('Y');
	$years = array();
	$years[] = $cur;
	for($i=0; $i<=8; $i++){
		$cur = $cur + 1;
		$years[] = $cur;
	} 
	
	return $years;
}

function smart_wordwrap($string, $width, $break = "<br>") {
    // split on problem words over the line length
    $pattern = sprintf('/([^ ]{%d,})/', $width);
    $output = '';
    $words = preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    foreach ($words as $word) {
        if (false !== strpos($word, ' ')) {
            // normal behaviour, rebuild the string
            $output .= $word;
        } else {
            // work out how many characters would be on the current line
            $wrapped = explode($break, wordwrap($output, $width, $break));
            $count = $width - (strlen(end($wrapped)) % $width);

            // fill the current line and add a break
            $output .= substr($word, 0, $count) . $break;

            // wrap any remaining characters from the problem word
            $output .= wordwrap(substr($word, $count), $width, $break, true);
        }
    }

    // wrap the final output
    return wordwrap($output, $width, $break);
}


function userdate($dated){
     
	 $dated = explode('-', $dated);
	 $YYYY  = $dated[0];
	 $MM = $dated[1];
	 $DD = $dated[2];
	 
	 $final = date("M j, Y", mktime(0,0,0,$MM,$DD, $YYYY));
	 return $final;
}

function getMonths($s,$e){
	
	$start    = new DateTime($s);
	$start->modify('first day of this month');
	$end      = new DateTime($e);
	$end->modify('first day of next month');
	$interval = DateInterval::createFromDateString('1 month');
	$period   = new DatePeriod($start, $interval, $end);
	return $period;
				
}

function timeInAmPm($dateTime){
     $final_time = date("H:i A", strtotime($dateTime));
	 return $final_time;
}

function timeInAmPmShort($dateTime){
     $final_time = date("H A", strtotime($dateTime));
	 return $final_time;
}

function timeDifferance($dateTime1,$dateTime2){
	
    $call_start = new DateTime($dateTime1);
	$call_end = new DateTime($dateTime2);

	$interval = date_diff($call_start,$call_end);
	return $interval->format('%h hour %i min %s sec');
}


function createDateRangeArray($strDateFrom,$strDateTo)
{
    // takes two dates formatted as YYYY-MM-DD and creates an
    // inclusive array of the dates between the from and to dates.

    // could test validity of dates here but I'm already doing
    // that in the main script

    $aryRange=array();

    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),     substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),     substr($strDateTo,8,2),substr($strDateTo,0,4));

    if ($iDateTo>=$iDateFrom)
    {
        array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
        while ($iDateFrom<$iDateTo)
        {
            $iDateFrom+=86400; // add 24 hours
            array_push($aryRange,date('Y-m-d',$iDateFrom));
        }
    }
    return $aryRange;
}


function manageString($str,$maxlenallowed,$wrapstr){
	
	$newstr = stripslashes(nl2br($str));
	$newstr = wordwrap($newstr,$wrapstr, "\n" ,true);
	
	if(strlen($newstr) > $maxlenallowed){
		$newstr = substr($newstr,0,$maxlenallowed).' ...';
	}else{
		$newstr = $newstr;
	}
	return $newstr;
	
} //end manageString()

function setWarp($str,$wrapstr){

	$newstr = stripslashes(nl2br($str));
	$newstr = wordwrap($newstr,$wrapstr,"\n",true);
	return $newstr;

}// end manageString($str,$wrapstr)

//START: Create a Croped Image 
function cropImage($nw, $nh, $source, $stype, $dest) {
       
          $size = getimagesize($source);
          $w = $size[0];
          $h = $size[1];
       
          switch($stype) {
              case 'gif':
              $simg = imagecreatefromgif($source);
              break;
              case 'jpg':
              $simg = imagecreatefromjpeg($source);
              break;
              case 'png':
              $simg = imagecreatefrompng($source);
              break;
          }
       
          $dimg = imagecreatetruecolor($nw, $nh);
       
          $wm = $w/$nw;
          $hm = $h/$nh;
       
          $h_height = $nh/3;
          $w_height = $nw/3;
       
         if($wm> $hm) {
       
              $adjusted_width = $w / $hm;
              $half_width = $adjusted_width / 2;
              $int_width = $half_width - $w_height;
       
              imagecopyresampled($dimg,$simg,-$int_width,0,0,0,$adjusted_width,$nh,$w,$h);
       
          } elseif(($w <$h) || ($w == $h)) {
       
              $adjusted_height = $h / $wm;
              $half_height = $adjusted_height / 3;
              $int_height = $half_height - $h_height;
       
              imagecopyresampled($dimg,$simg,0,-$int_height,0,0,$nw,$adjusted_height,$w,$h);
       
          } else {
              imagecopyresampled($dimg,$simg,0,0,0,0,$nw,$nh,$w,$h);
          }
       
          imagejpeg($dimg,$dest,100);
      }
//END: Create a Croped Image


function commonDbFunction($selectfield,$tablename,$wherefield,$wherevalue){

	global $db;
	$qry = "select $selectfield FROM tbl_".$tablename." WHERE $wherefield = '".$wherevalue."'";
	$rs = $db->Execute($qry);
	
	return $rs->fields[$selectfield];
	
}// end commonDbFunction()

function generateRandomString($length = 6, $letters = '1234567890qwertyuiopasdfghjklzxcvbnm')
  {
      $s = '';
      $lettersLength = strlen($letters)-1;
     
      for($i = 0 ; $i < $length ; $i++)
      {
      $s .= $letters[rand(0,$lettersLength)];
      }
     
      return $s;
  }
  
 function is_email($email){

	$email = strtolower($email);
	if (!ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email) || empty($email)){
		return false;
	}else{
		return true;
	}
}//end is_email()
 
 
 

/* Function to Delete the contents and then Folder */

function rmdir_r ( $dir, $DeleteMe = TRUE ){

	if ( ! $dh = @opendir ( $dir ) ) return;
	
	while ( false !== ( $obj = readdir ( $dh ) ) ){
		if ( $obj == '.' || $obj == '..') continue;
		if ( ! @unlink ( $dir . '/' . $obj ) ) rmdir_r ( $dir . '/' . $obj, true );
	}
	
	closedir ( $dh );
	if ( $DeleteMe ){
		@rmdir ( $dir );
	}
}


/* Function to Delete the contents only */

function rmdircontents_r ( $dir, $DeleteMe = TRUE ){

	if ( ! $dh = @opendir ( $dir ) ) return;
	
	while ( false !== ( $obj = readdir ( $dh ) ) ){
		if ( $obj == '.' || $obj == '..') continue;
		if ( ! @unlink ( $dir . '/' . $obj ) ) rmdir_r ( $dir . '/' . $obj, true );
	}
	
	closedir ( $dh );
	
}

/* Funtion to copy a folder and its files from source to destination  */

function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
} 

function count_words($str) 
 {
 $no = count(explode(" ",$str));
 return $no;
 }

?>