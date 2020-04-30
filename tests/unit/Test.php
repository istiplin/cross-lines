<?php
namespace tests\unit;

use models\Line;
use models\Cell;
use models\Section;

class Test extends \Codeception\Test\Unit
{
    public function testSolveLine()
    {
        $solves = require 'tests/_data/lineSolves.php';

        foreach($solves as $key=>$solve)
        {
            if (array_key_exists(2, $solve))
            {
				$solve[1] = str_replace(' ','',$solve[1]);
				$solve[2] = str_replace(' ','',$solve[2]);
				
                $this->checkSolveLine($key,$solve[0],$solve[1],false,$solve[2]);
                $this->checkSolveLine($key,$solve[0],$solve[1],true,$solve[2]);
            }
        }
    }
	
    private function checkSolveLine($ind,$numbers,$cellsStr,$isMirror,$result)
    {			
        $line = new Line($ind,$numbers,$cellsStr,false,null,$isMirror);
		
		$numView = $line->getNumbers()->getLengthView();
        
		if ($line->trySolveTest())
			$newCells = $line->getCellsView();
		else
			$newCells = 'error';
			
		if ($newCells=='error' AND $result!='error')
		{
			$newline = new Line($ind,$numbers,$cellsStr,false,null,$isMirror);
			$newline->solveTest();
		}
		
		if ($isMirror)
		{
			if ($result!='error')
				$result = strrev($result);
			$cellsStr = strrev($cellsStr);
		}
			
		$begMess = '';
		$message = 'message: '.$ind.PHP_EOL.
						$numView.PHP_EOL.
						$cellsStr.PHP_EOL.
						$result.' is correct'.PHP_EOL.
						$newCells.' no correct';
		if ($isMirror)
			$begMess = 'MIRROR';
		expect($begMess.' '.$message,$newCells)->equals($result);
    }
}