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

    //echo $model->numbers->getMinPos(4).'-'.$model->numbers->getMaxPos(4).'<br>';

    $model->cells->view();

    $model->resolve();

    $model->numbers->printBounds();

    $model->cells->view();

    $model->groups->view();


    //for ($i=0; $i<$model->numbers->count; $i++) {
    //    echo $i . ' ' . $model->numbers[$i]->getPos('min') . '-';
    //    echo $model->numbers[$i]->getPos('max') . '<br>';
    //}
