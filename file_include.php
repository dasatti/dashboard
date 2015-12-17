<?php 
	session_start();
	ini_set("memory_limit","500M");
	header('Cache-control: private, no-cache');
	header('Expires: 0');
	header('Pragma: no-cache');
	error_reporting(0);
	define('ROOT',"");
	include('include/siteconfig.inc.php');
	include('include/sitefunction.php');
	include('include/dbfunctions.php');
	include("include/libmail.php");
	include("classes/thumbnail.class.php");
	require_once 'include/ThumbLib.inc.php';
?>