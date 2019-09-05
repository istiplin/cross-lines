<?php
    include 'vendor/autoload.php';
    
    $dataName = 'fieldResolves.php';
    $datas = require 'tests/_data/'.$dataName;
    
    $fieldInd = 2;
    
    $data = $datas[$fieldInd];

    $model = new models\Field($data[0], $data[1],$data['name']);
    
    echo $model->name.'<br>';
    
    echo $model->sizeView();
    
    $model->resolve();
    
    echo $model->view;