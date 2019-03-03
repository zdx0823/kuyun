<?php 

include '../public/conf/connect.php';
include 'function.php';

if (isset($_GET) && !empty($_GET)) {
	$id = $_GET['id'];

	$sel = "SELECT `path` FROM `".PREFIX.FILE."` WHERE `id` = {$id}";
	$sql = mysql_query($sel);
	$path = mysql_fetch_array($sql)[0];

	$name = substr($path, strrpos($path, '/')+1);
	$path = substr($path, 0, strrpos($path, '/'));
	
	download($path, $name);
}

function download($path, $file) {
	$real = getCode($path.'/'.$file);
	if(!file_exists($real))
		return false;

	$size = filesize($real);
	$size2 = $size-1;
	$range = 0;
	if(isset($_SERVER['HTTP_RANGE'])) {
		header('HTTP /1.1 206 Partial Content');
		$range = str_replace('=','-',$_SERVER['HTTP_RANGE']);
		$range = explode('-',$range);
		$range = trim($range[1]);
		header('Content-Length:'.$size);
		header('Content-Range: bytes '.$range.'-'.$size2.'/'.$size);
	} else {
		header('Content-Length:'.$size);
		header('Content-Range: bytes 0-'.$size2.'/'.$size);
	}
	header('Accenpt-Ranges: bytes');
	header('Content-Type: application/octet-stream');
	header("Cache-control: public");
	header("Pragma: public");
	$ua = $_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/MSIE/',$ua)) {
		$ie_filename = str_replace('+','%20',urlencode($file));
		header('Content-Disposition:attachment; filename='.$ie_filename);
	} else {
		header('Content-Disposition:attachment; filename='.$file);
	}
	$fp = fopen($real,'rb+');
	fseek($fp,$range);
	while(!feof($fp)) { 
		set_time_limit(0);
		print(fread($fp,1024));
		ob_flush();
		flush();
	}
	fclose($fp);
}