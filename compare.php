<?php

	require "main.php";

	$submitted = isset($_POST["submitted"]) && $_POST["submitted"] == "yes" ? true : false;	
	$error = '';
	$content = '';
	$allTableNames = (object) array();

	// helper methods
	function getJSON($file) {
		return json_decode(file_get_contents($file));
	}
	function formatAsTable($title, $rows) {
		$markup = '';
		$markup .= '<table class="table table-bordered">';
		$markup .= '<tr><td colspan="10"><h2>'.$title.'</h2></td></tr>';
		$numOfRows = count($rows);
		for ($i=0; $i < $numOfRows; $i++) { 
			$markup .= $rows[$i];
		}
		$markup .= '</table>';
		return $markup;
	}
	function columns($arr) {
		$markup = '<tr>';
		$numOfColumns = count($arr);
		for ($i=0; $i < $numOfColumns; $i++) { 
			$markup .= '<th>'.$arr[$i].'</th>';
		}
		return $markup.'</tr>';
	}
	function equal($criteria, $value1, $value2, $dontShowIfCorrect = false) {
		$status = $value1 === $value2;
		if($dontShowIfCorrect && $status) return '';
		return '
			<tr class="'.($status ? 'yes' : 'no').'">
				<td>'.$criteria.'</td>
				<td>'.$value1.'</td>
				<td>'.$value2.'</td>
			</tr>
		';
	}
	function equalKeyInObject($key, $obj1, $obj2, $dontShowIfCorrect = false) {
		$status = isset($obj1->$key) && isset($obj2->$key);
		if($dontShowIfCorrect && $status) return '';
		return '
			<tr class="'.($status ? 'yes' : 'no').'">
				<td>'.$key.'</td>
				<td>'.(isset($obj1->$key) ? "yes" : "no").'</td>
				<td>'.(isset($obj2->$key) ? "yes" : "no").'</td>
			</tr>
		';
	}

	// diffs
	function main($d1, $d2) {
		return formatAsTable('Main', array(
			columns(array('', $d1->database, $d2->database)),
			equal("Num of tables", $d1->numOfTables, $d2->numOfTables)
		));
	}
	function tables($d1, $d2) {
		global $allTableNames;
		foreach($d1->tables as $name => $table) {
			if(!isset($allTableNames->$name)) $allTableNames->$name = 'check';
		}
		foreach($d2->tables as $name => $table) {
			if(!isset($allTableNames->$name)) $allTableNames->$name = 'check';
		}
		$rows = array();
		foreach($allTableNames as $name => $value) {
			array_push($rows, equalKeyInObject($name, $d1->tables, $d2->tables));
		}
		return formatAsTable('Tables', array_merge(
			array(columns(array('', $d1->database, $d2->database))),
			$rows
		));
	}
	function tablesFormat($d1, $d2) {
		global $allTableNames;
		$markup = '';
		foreach($allTableNames as $name => $value) {
			if(isset($d1->tables->$name) && isset($d2->tables->$name)) {
				$table1 = $d1->tables->$name;
				$table2 = $d2->tables->$name;
				$allFields = (object) array();
				foreach($table1->fields as $fieldName => $field) {
					if(!isset($allFields->$fieldName)) $allFields->$fieldName = 'check';
				}
				foreach($table2->fields as $fieldName => $field) {
					if(!isset($allFields->$fieldName)) $allFields->$fieldName = 'check';
				}
				$rows = array();
				array_push($rows, equal("Number of records", count($table1->values), count($table2->values)));
				foreach($allFields as $fieldName => $value) {
					array_push($rows, equalKeyInObject($fieldName, $table1->fields, $table2->fields));
				}
				$markup .= formatAsTable('Table: '.$name, array_merge(
					array(columns(array('', $d1->database, $d2->database))),
					$rows
				));
			}
		}
		return $markup;
	}

	if($submitted) {
		if(isset($_FILES) && $_FILES["file1"] && $_FILES["file2"] && $_FILES["file1"]["name"] !== "" && $_FILES["file2"]["name"] !== "") {
			$data1 = getJSON($_FILES["file1"]["tmp_name"]);
			$data2 = getJSON($_FILES["file2"]["tmp_name"]);
			$content .= main($data1, $data2);
			$content .= tables($data1, $data2);
			$content .= tablesFormat($data1, $data2);
		} else {
			$error = '<div class="alert alert-error">Please choose files!</div>';
			$content = view("tpl/compare-form.html", array(
				"error" => $error
			));
		}
	} else {
		$content = view("tpl/compare-form.html", array(
			"error" => $error
		));
	}

	echo view("tpl/layout.html", array(
		"content" => $content
	));

?>
