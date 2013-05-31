<?php

	require "main.php";

	$submitted = isset($_POST["submitted"]) && $_POST["submitted"] == "yes" ? true : false;	
	$error = '';
	$content = '';
	$allTableNames = (object) array();
	$data1Content = '{}';
	$data2Content = '{}';

	// helper methods
	function getJSON($file, &$var) {
		$fileContent = $var = file_get_contents($file);
		return json_decode($fileContent);
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
	function headers($arr) {
		$markup = '<tr>';
		$numOfColumns = count($arr);
		for ($i=0; $i < $numOfColumns; $i++) { 
			$markup .= '<th>'.$arr[$i].'</th>';
		}
		return $markup.'</tr>';
	}
	function row($arr) {
		$markup = '<tr>';
		$numOfColumns = count($arr);
		for ($i=0; $i < $numOfColumns; $i++) { 
			$markup .= '<td>'.$arr[$i].'</td>';
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
	function getTablesNamesDropDown() {
		global $allTableNames;
		$markup = '';
		foreach ($allTableNames as $name => $value) {
			$markup .= '<option value="'.$name.'">'.$name.'</option>';
		}
		return $markup;
	}

	// diffs
	function main($d1, $d2) {
		return formatAsTable('Main', array(
			headers(array('', $d1->database, $d2->database)),
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
			array(headers(array('', $d1->database, $d2->database))),
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
				array_push($rows, row(array('Fields', '', '')));
				foreach($allFields as $fieldName => $value) {
					array_push($rows, equalKeyInObject($fieldName, $table1->fields, $table2->fields));
				}
				$markup .= formatAsTable('Table: '.$name, array_merge(
					array(headers(array('', $d1->database, $d2->database))),
					$rows
				));
			}
		}
		return $markup;
	}

	if($submitted) {
		if(isset($_FILES) && $_FILES["file1"] && $_FILES["file2"] && $_FILES["file1"]["name"] !== "" && $_FILES["file2"]["name"] !== "") {
			$data1 = getJSON($_FILES["file1"]["tmp_name"], $data1Content);
			$data2 = getJSON($_FILES["file2"]["tmp_name"], $data2Content);
			$main = main($data1, $data2);
			$tables = tables($data1, $data2);
			$tablesFormat = tablesFormat($data1, $data2);
			$content .= view("tpl/compare-form-actions.html", array(
				"compareValuesTables" => getTablesNamesDropDown()
			));
			$content .= '<div id="diffs">';
			$content .= $main;
			$content .= $tables;
			$content .= $tablesFormat;
			$content .= '</div>';
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
		"content" => $content,
		"d1" => $data1Content,
		"d2" => $data2Content
	));

?>
