<?php
	$cellsStr = null;
	if ($_POST['cells']!==null)
	{
		$cellsStr = [];
		$cells = $_POST['cells'];
		foreach ($cells as $line)
		{
			$lineStr = '';
			foreach ($line as $cell)
				$lineStr.=$cell;
			$cellsStr[] = $lineStr;
		}
	}
	
	//print_r($cellsStr);
	//die();
	include '../vendor/autoload.php';

	$field = new models\FastField($_POST['horNums'], $_POST['vertNums'], $cellsStr);
	//$field->maxDuration = 0.24;
	$field->maxDuration = 0.12;
	//$field->maxDuration = 0.001;
	$field->solve();
	$responce['horNums'] = json_encode($_POST['horNums']);
	$responce['vertNums'] = json_encode($_POST['vertNums']);
	$responce['cells'] = $field->getCells();
	
	echo json_encode($responce);