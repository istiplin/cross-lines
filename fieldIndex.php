<?php
    include 'vendor/autoload.php';
    
    $dataName = 'fieldResolves.php';
    $datas = require 'tests/_data/'.$dataName;
    
    $fieldInd = 4;
    
    $data = $datas[$fieldInd];

    $field = new models\Field($data[0], $data[1],$data[2],$data['name']);
    
    echo $field->name.'<br>';
    
    echo $field->sizeView().'<br>';
    
    if ($field->resolve())
		echo 'SUCCESS!';
	else
		echo 'ERROR!';
	echo $field->view;
	
	$field->unknownCellsArrView();