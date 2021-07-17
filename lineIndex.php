<html>
    <head></head>
    <body style="font-family: monospace; font-size:12pt">
<?php
    include 'vendor/autoload.php';

    $dataName = 'lineSolves.php';
    $datas = require 'tests/_data/'.$dataName;

    //$lineInd = 159;
    $lineInd = 155;

    $isMirror = false;
	
    $data = $datas[$lineInd];
	
    $line = new models\Line($lineInd,$data[0],$data[1],null,$isMirror);
    echo $line->getNumbersView().'<br>';
    echo $line->getCells().'<br>';
    echo '<br>';
    
    
    $res = $line->solve().'<br>';
    echo '<br>';
    echo $res;
?>
    </body>
</html>