<?php
    include 'vendor/autoload.php';

    $dataName = 'lineResolves.php';
    $datas = require 'tests/_data/'.$dataName;

    //$lineInd = 400;
	//$lineInd = 68;
	$lineInd = 53;

    $data = $datas[$lineInd];

    $isMirror = false;

    $line = new models\Line($lineInd,$data[0],$data[1],false,null,$isMirror);
	echo $line->getCells()->getData().'<br>';
    $line->resolve();
    echo $line->getCells()->getData();