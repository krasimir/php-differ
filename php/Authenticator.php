<?php

	class Authenticator {

		public static $username = "__";
		public static $password = "__";
		
		public static function check() {
			if (
				isset($_SERVER['PHP_AUTH_USER']) &&
				isset($_SERVER['PHP_AUTH_PW']) &&
				$_SERVER['PHP_AUTH_USER'] == self::$username &&
				$_SERVER['PHP_AUTH_PW'] == self::$password
			) {
				return true;
			} else {
				header('WWW-Authenticate: Basic realm="Please login."');
				header('HTTP/1.0 401 Unauthorized');
				die("Wrong username or password!");
			}
		}
	}

?>