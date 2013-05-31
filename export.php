<?php

	require "main.php";

	$submitted = isset($_POST["submitted"]) && $_POST["submitted"] == "yes" ? true : false;
	$host = isset($_POST["host"]) && $_POST["host"] != "" ? $_POST["host"] : false;
	$username = isset($_POST["username"]) && $_POST["username"] != "" ? $_POST["username"] : false;
	$password = isset($_POST["password"]) ? $_POST["password"] : false;
	$dbname = isset($_POST["dbname"]) && $_POST["dbname"] != "" ? $_POST["dbname"] : false;
	$exportValues = isset($_POST["export-values"]) && $_POST["export-values"] != "" ? $_POST["export-values"] : false;
	$error = '';
	$content = '';
	$data = '';

	if($submitted) {		
		if($host !== false && $username !== false && $password !== false && $dbname !== false) {
			// connecting to the database
			if(@mysql_connect($host, $username, $password) === false) {
				$content = '
					<div class="alert alert-error">I can\'t connect to '.$host.' with '.$username.'/'.$password.'</div>
					<a href="export.php" class="btn">Try again.</a>
				';
			// selecting the database
			} else if(@mysql_select_db($dbname) === false) {
				$content = '
					<div class="alert alert-error">I can\'t select database with name '.$dbname.'</div>
					<a href="export.php" class="btn">Try again.</a>
				';
			} else {
				$data = (object) array(
					"numOfTables" => 0,
					"tables" => (object) array()
				);
				// getting tables
				$res = mysql_query("SHOW TABLES");
				while($row = mysql_fetch_row($res)) {
					$data->numOfTables += 1;
					$data->tables->$row[0] = (object) array();
				}
				// getting table fields & values
				foreach($data->tables as $table => $obj) {
					$obj->fields = (object) array();
					$obj->values = array();
					$res = mysql_query("SHOW COLUMNS FROM ".$table);
					while($row = mysql_fetch_row($res)) {
						$obj->fields->$row[0] = (object) array(
							"name" => $row[0],
							"type" => $row[1]
						);
					}
					if($exportValues === "yes") {
					$res = mysql_query("SELECT * FROM ".$table);
						while($row = mysql_fetch_row($res)) {
							array_push($obj->values, $row);
						}
					}
				}
				header('Content-Description: File Transfer');
				header('Content-Type: application/json');
			    header('Content-Disposition: attachment; filename='.$dbname."_".date("Y-m-d").".json");
			    header('Expires: 0');
			    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			    header('Pragma: public');
				exit(json_encode($data));
			}
		} else {
			$error = '<div class="alert alert-error">Missing data!</div>';
		}
	} else {
		$content = view("tpl/export-form.html", array(
			"error" => $error
		));
	}

	echo view("tpl/layout.html", array(
		"content" => $content
	));

?>
