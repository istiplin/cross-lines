<?php
namespace tests\unit;

use models\Line;
use models\Cell;
use models\Section;

class Test extends \Codeception\Test\Unit
{
    public function testResolveLine()
    {
        $resolves = require 'tests/_data/lineResolves.php';

        foreach($resolves as $key=>$resolve)
        {
            if (array_key_exists(2, $resolve))
            {
                $this->checkResolveLine($key,$resolve[0],$resolve[1],false,$resolve[2]);
                $this->checkResolveLine($key,$resolve[0],$resolve[1],true,$resolve[2]);
            }
        }
    }
	
    private function checkResolveLine($ind,$numbers,$cellsStr,$isMirror,$result)
    {
        $model = new Line($ind,$numbers,$cellsStr,true,null,$isMirror);
        $model->resolve($cellsStr);
        $newCells = $model->cellsView;
        $message = 'message: '.$ind.' '.$cellsStr.'->'.$newCells;
        $begMess = '';
        if ($isMirror)
            $begMess = 'MIRROR';
        expect($begMess.' '.$message,$newCells)->equals($result);
    }
}