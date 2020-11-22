<?php

    ini_set('max_execution_time',50);

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
        
    $horNums = $_POST['horNums'];
    $vertNums = $_POST['vertNums'];

    //print_r($cellsStr);
    //die();
    
    include '../vendor/autoload.php';

    if (empty($_POST))
    {
        $datas = require '../tests/_data/fieldSolves.php';
        //$fieldInd = 8;
        $fieldInd = 7;
        $data = $datas[$fieldInd];
        $horNums = $data[0];
        $vertNums = $data[1];
        $cellsStr = null;
    }
    
    

    $field = new models\FieldCells($horNums, $vertNums, $cellsStr);
    
    //$field->maxDuration = 1.0;
    
    $field->solve();
    //$responce['horNums'] = json_encode($_POST['horNums']);
    //$responce['vertNums'] = json_encode($_POST['vertNums']);
    $responce['cells'] = $field->getCells();

    echo json_encode($responce);