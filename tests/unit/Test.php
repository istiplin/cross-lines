<?php
namespace tests\unit;

use models\Line;
use models\LineData;

class Test extends \Codeception\Test\Unit
{
    public function testSolveLine()
    {
        $solves = require 'tests/_data/lineSolves.php';

        foreach($solves as $key=>$solve)
        {
            if (!array_key_exists(2, $solve))
                continue;
		
            $line = new Line($key,$solve[0],$solve[1],$solve[2]);
            $this->checkSolveLine($line);
 
            $line = new Line($key,$solve[0],$solve[1],$solve[2],true);
            $this->checkSolveLine($line);

        }
    }

    private function checkSolveLine(Line $line)
    {
        try{
            $baseCells = $line->getCells();
            $result = $line->solve();
        } catch(\Exception $e) {
            throw new \Exception('error ind:'.$line->getInd().$line->getMirrorStatus());
        }
        

        $message = $line->getMirrorStatus().'message: '.$line->getInd().PHP_EOL.
                                $line->getNumbersView().PHP_EOL.
                                $baseCells.PHP_EOL.
                                $line->getExpectedResult().' is expected result'.PHP_EOL.
                                $result.' no expected result'.PHP_EOL;

        expect($message,$result)->equals($line->getExpectedResult());
    }
}