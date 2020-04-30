<?php
    include 'vendor/autoload.php';
    
    $dataName = 'fieldSolves.php';
    $datas = require 'tests/_data/'.$dataName;
    
    $fieldInd = 7;
    
    $data = $datas[$fieldInd];
	
    $field = new models\FastField($data[0], $data[1],$data[2],$data['name']);
    
	$isSuccess = $field->solve();
	echo PHP_EOL;
	echo PHP_EOL;

	$cells = $field->getCells();
	for ($y=0; $y<count($cells); $y++)
	{
		for($x=0; $x<count($cells[$y]); $x++)
			echo $cells[$y][$x];
		echo PHP_EOL;
	}
	echo PHP_EOL;
    if ($isSuccess)
		echo 'SUCCESS!'.PHP_EOL;
	else
		echo 'ERROR!'.PHP_EOL;

	echo 'Duration: '.$field->duration.' sec'.PHP_EOL;
	echo 'Memory: '.memory_get_usage()/(1024*1024).' MB'.PHP_EOL;