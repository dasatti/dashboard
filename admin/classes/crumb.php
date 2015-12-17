<?php

// +--------------------------------------------------------------------------+
// | crumb version 0.1.0.1 - 2003/01/04                                       |
// | by Michael J. Pawlowsky <mjpawlowsky@yahoo.com>                          |
// +--------------------------------------------------------------------------+
// | Copyright (c) 2003 RC Online Canada                                      |
// +--------------------------------------------------------------------------+
// | License:  GNU/GPL - http://www.gnu.org/copyleft/gpl.html                 |
// +--------------------------------------------------------------------------+
// | Original release available on PHP Classes:                               |
// |    http://www.phpclasses.org/                                            |
// |                                                                          |
// +--------------------------------------------------------------------------+
//
// 2003/01/04 - 0.1.0.1 fixed undefined tstr in addCrumb


class crumb {

	
	/**
	 * @return void
	 * @param level int
	 * @param title string
	 * @param url string
	 * @param post boolean	 
	 * @desc Add a bread crumb to the session array. If post is true add the $_POST args to the URL.
	 */	
	function addCrumb($level, $title, $url, $post = false) {
		
		$tstr = "";
		
		if (isset($_SESSION['crumbs'][$level])){
			unset($_SESSION['crumbs'][$level]);
		}
		
		if($post){
			if(strpos($url,"?")){
				$tstr = "&";
			}else{
				$tstr = "?";
			}
			
			foreach($_POST as $key => $value) {
				$tstr.=$key."=".urlencode($value)."&";
			}
			// pop off the last &
			$tstr = rtrim ($tstr, "&");		
		}
		
		
		$tmp = array("title" => $title, "url" => $url . $tstr);
		$_SESSION['crumbs'][$level] = $tmp;
	} //end addCrumb()
	
	
	/**
	 * @return void
	 * @param level int
	 * @desc Deletes a bread crumb.
	 */
	function delCrumb($level) {		
		if (isset($_SESSION['crumbs'][$level])){
			unset($_SESSION['crumbs'][$level]);
		}
	} //end delCrumb()

	
	/**
	 * @return void
	 * @param cur_level int
	 * @desc Print out the current crumb trail from $cur_level on down.
	 */
	function printTrail($cur_level) {
		
		echo "<span  style=\"font-weight:bold\" class=\"crumb\" >";			
		for ($i=1; $i != $cur_level+1; $i++){
			
			if (isset($_SESSION['crumbs'][$i])){			
				if ($i != $cur_level){
						echo "<a href=\"". $_SESSION['crumbs'][$i]['url'] . "\" style=\"font-weight:bold;font-size:11\">";
						echo $_SESSION['crumbs'][$i]['title'];
						echo "</a>";
				}else{
					echo $_SESSION['crumbs'][$i]['title'];	
					echo "</span>";				
				}
				if ($i != $cur_level){
					echo "&nbsp;&gt;&gt;&nbsp;";
				}				
			}
		}
		echo "</span>";	
	} // end printTrail()
	
} //end class crumb




///////////////////////////////             Manage Hijab Style class ///////////////////////////////////////////


class hijabCrumb {

	
	/**
	 * @return void
	 * @param level int
	 * @param title string
	 * @param url string
	 * @param post boolean	 
	 * @desc Add a bread crumb to the session array. If post is true add the $_POST args to the URL.
	 */	
	function addHijabCrumb($level, $title, $url, $post = false) {
		
		$tstr = "";
		
		if (isset($_SESSION['hcrumbs'][$level])){
			unset($_SESSION['hcrumbs'][$level]);
		}
		
		if($post){
			if(strpos($url,"?")){
				$tstr = "&";
			}else{
				$tstr = "?";
			}
			
			foreach($_POST as $key => $value) {
				$tstr.=$key."=".urlencode($value)."&";
			}
			// pop off the last &
			$tstr = rtrim ($tstr, "&");		
		}
		
		
		$tmp = array("title" => $title, "url" => $url . $tstr);
		$_SESSION['hcrumbs'][$level] = $tmp;
	} //end addCrumb()
	
	
	/**
	 * @return void
	 * @param level int
	 * @desc Deletes a bread crumb.
	 */
	function delHijabCrumb($level) {		
		if (isset($_SESSION['hcrumbs'][$level])){
			unset($_SESSION['hcrumbs'][$level]);
		}
	} //end delCrumb()

	
	/**
	 * @return void
	 * @param cur_level int
	 * @desc Print out the current crumb trail from $cur_level on down.
	 */
	function printHijabTrail($cur_level) {
		
		echo "<span  style=\"font-weight:bold\" class=\"crumb\" >";			
		for ($i=1; $i != $cur_level+1; $i++){
			
			if (isset($_SESSION['hcrumbs'][$i])){			
				if ($i != $cur_level){
						echo "<a href=\"". $_SESSION['hcrumbs'][$i]['url'] . "\" style=\"font-weight:bold;font-size:11\">";
						echo $_SESSION['hcrumbs'][$i]['title'];
						echo "</a>";
				}else{
					echo $_SESSION['hcrumbs'][$i]['title'];	
					echo "</span>";				
				}
				if ($i != $cur_level){
					echo "&nbsp;&gt;&gt;&nbsp;";
				}				
			}
		}
		echo "</span>";	
	} // end printTrail()
	
} //end class crumb

?>