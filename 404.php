<?php
	function getRequestUri() {
	  if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { 
		 // check this first so IIS will catch 
		 $requestUri = $_SERVER['HTTP_X_REWRITE_URL']; 
	   } elseif (isset($_SERVER['REDIRECT_URL'])) { 
		 // Check if using mod_rewrite 
		 $requestUri = $_SERVER['REDIRECT_URL']; 
	   } elseif (isset($_SERVER['REQUEST_URI'])) { 
		 $requestUri = $_SERVER['REQUEST_URI']; 
	   } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { 
		 // IIS 5.0, PHP as CGI 
		 $requestUri = $_SERVER['ORIG_PATH_INFO']; 
		 if (!empty($_SERVER['QUERY_STRING'])) { 
		   $requestUri .= '?' . $_SERVER['QUERY_STRING']; 
		 } 
	   } 
	   return $requestUri; 
	 }
	
	$flag = false;

	$file = str_replace("/","",getRequestUri());
	$files = explode(".",$file);
	if (count($files) == 2){
 		if (substr($files[0],0,10) == "MP_verify_" && $files[1]=="txt" && strlen(substr($files[0],10))== 16){
			//自动创建
			$flag = true;
			header('Content-type: text/plain');
			header('Content-Length: 16');
			header('HTTP/1.1 200 OK');
			$file = fopen($file,'w');
			fwrite($file,substr($files[0],10));
			fclose($file);
			echo substr($files[0],10);
			exit;
		}
	}

	if (!$flag){
  
?>
	<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>404</title>
<style>
	body{
		background-color:#444;
		font-size:14px;
	}
	h3{
		font-size:60px;
		color:#eee;
		text-align:center;
		padding-top:30px;
		font-weight:normal;
	}
</style>
</head>

<body>
<h3>404，您请求的文件不存在!</h3>
</body>
</html>

<?
	}
?>