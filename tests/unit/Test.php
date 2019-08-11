<?php
namespace tests\unit;

use models\Line;
use models\Cell;
use models\Section;

class Test extends \Codeception\Test\Unit
{
    public function testResolve()
    {
        $resolves = require 'tests/_data/resolves.php';

        foreach($resolves as $key=>$resolve)
        {
            if (array_key_exists(2, $resolve))
            {
                $this->checkResolve($key,$resolve[0],$resolve[1],false,$resolve[2]);
                $this->checkResolve($key,$resolve[0],$resolve[1],true,$resolve[2]);
            }
        }
    }
	
    private function checkResolve($ind,$numbers,$cells,$isMirror,$result)
    {
        $model = new Line($ind,$numbers,$cells,$isMirror);
        $model->resolve();
        $newCells = $model->cellsView;
        $message = 'message: '.$ind.' '.$cells.'->'.$newCells;
        $begMess = '';
        if ($isMirror)
            $begMess = 'MIRROR';
        expect($begMess.' '.$message,$newCells)->equals($result);
    }
}