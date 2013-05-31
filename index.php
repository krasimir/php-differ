<?php

	require "config/config.php";
	require "php/Authenticator.php";

	Authenticator::$username = AUTH_USER;
	Authenticator::$password = AUTH_PASSWORD;

	if(!Authenticator::check()) {
		exit("Please login!");
	}

?>
<!DOCTYPE html>
<html>
	<head>
		
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="imagetoolbar" content="no" />
		<meta name="author" content="Krasimir Tsonev" />
		<meta name="copyright" content="Krasimir Tsonev" />
		<meta name="robots" content="follow,index" />
		<meta name="title" content="PHP Differ" />
		<meta name="keywords" content="PHP Differ" lang="en-us" />
        <meta name="description" content="PHP Differ" />
        <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, maximum-scale=1.0" />
        
		<title>PHP Differ</title>

        <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
		
	</head>
	<body class="main">
		
		<div class="container">
			<h1>PHP Differ</h1>
			<hr />
			<ul class="nav nav-tabs nav-stacked">
				<li><a href="#">Export</a></li>
				<li><a href="#">Compare</a></li>
			</ul>
		</div>

	</body>
</html>