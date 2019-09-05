<?php
    include 'vendor/autoload.php';

    $dataName = 'lineResolves.php';
    $datas = require 'tests/_data/'.$dataName;

    $lineInd = 400;

    $data = $datas[$lineInd];

    $isMirror = false;

    $model = new models\Line($lineInd,$data[0],$data[1],true,null,$isMirror);

    $model->resolve(1,1);