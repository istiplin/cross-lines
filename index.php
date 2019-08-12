<?php
    include 'vendor/autoload.php';

    $dataName = 'resolves.php';
    //$dataName = 'possibleNumbers.php';
    $datas = require 'tests/_data/'.$dataName;

    //$lineInd = 103;

	//$lineInd = 104;

    //$lineInd = 402;

    //$lineInd = 74;

    $lineInd = 263; //error

    $data = $datas[$lineInd];

    $isMirror = false;

    $model = new models\Line($lineInd,$data[0],$data[1],$isMirror);

    $model->cells->view();

    $model->numbers->printBounds();

    $model->groups->deleteGroupNumbersFromSideCell2();
    $model->groups->view();

    $model->resolve();

    $model->numbers->printBounds();

    $model->cells->view();

    $model->groups->view();

