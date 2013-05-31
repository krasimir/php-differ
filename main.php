<?php

	require "config/config.php";
	require "php/Authenticator.php";
	require "php/View.php";

	Authenticator::$username = AUTH_USER;
	Authenticator::$password = AUTH_PASSWORD;

	if(!Authenticator::check()) {
		exit("Please login!");
	}

?>