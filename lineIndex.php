<?php
    include 'vendor/autoload.php';

    $dataName = 'lineSolves.php';
    $datas = require 'tests/_data/'.$dataName;

    $lineInd = 159;

    $isMirror = true;
	
    $data = $datas[$lineInd];
	
    $line = new models\Line($lineInd,$data[0],$data[1],null,$isMirror);
    
    echo $line->getView().'<br>';
    $line->solveTest(1,1);
    echo $line->getView().'<br>';

    echo '<br>end!<br>';
    
    $output = $line->getOutput();
    print_r($output->getChangeIds());