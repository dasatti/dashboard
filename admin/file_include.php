<?php
	define('ROOT',"../");
	ini_set("memory_limit","10M");
	session_start();
	header('Cache-control: private, no-cache');
	header('Expires: 0');
	header('Pragma: no-cache');
	
	
	include('../adodb/adodb.inc.php');
	include('../include/siteconfig.inc.php');
	$db = ADONewConnection('mysql');
	$db->Connect(DBHOST,DBUSER,DBPASS,DBNAME) or die("Database not found! please install your application properly");
	
	include('../include/sitefunction.php');
	include('../include/dbfunctions.php');

	include('admin_security.php');
	include("include/libmail.php");
	include("classes/thumbnail.class.php");
	include("classes/crumb.php");
	require_once 'include/ThumbLib.inc.php';
	
?>