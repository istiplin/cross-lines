<?php
    include 'vendor/autoload.php';

    $dataName = 'lineSolves.php';
    $datas = require 'tests/_data/'.$dataName;

	$lineInd = 160;
	
    $data = $datas[$lineInd];

    $isMirror = false;

	$data[1] = str_replace(' ','',$data[1]);
	
    $line = new models\Line($lineInd,$data[0],$data[1],false,null,$isMirror);
	echo $line->getView().'<br>';
    $line->solveTest(1,1);
    echo $line->getView().'<br>';