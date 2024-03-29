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
    
    

    $field = new models\Field($horNums, $vertNums, $cellsStr);
    
    //$field->maxDuration = 0.1;
    
    $field->solve();
    $responce['begCells'] = $_POST['cells'];
    $responce['horNums'] = json_encode($horNums);
    $responce['vertNums'] = json_encode($vertNums);
    $responce['cells'] = $field->getCells();

    echo json_encode($responce);