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
        $line->trySolve();
        $lineData = $line->getData();

        $message = $lineData->getMirrorStatus().'message: '.$line->getInd().PHP_EOL.
                                $lineData->getNumbersView().PHP_EOL.
                                $lineData->getCellsStr().PHP_EOL.
                                $lineData->getExpectedResult().' is expected result'.PHP_EOL.
                                $lineData->getResult().' no expected result'.PHP_EOL.
                                $lineData->getErrorMessage();
        
        expect($message,$lineData->getResult())->equals($lineData->getExpectedResult());
    }
}