<?php 
	// var_dump($_POST);exit;
	require 'config.php';
	error_reporting(0);
	header("Content-type: text/html;charset=utf-8");
	$conn = @mysql_connect(HOSTNAME, USERNAME, PASSWORD);
	mysql_select_db(DATABASE);
	mysql_query('SET NAMES UTF8');
	date_default_timezone_set('PRC');
	session_start();
 ?>