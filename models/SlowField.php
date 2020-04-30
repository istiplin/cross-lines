<?php
namespace models;

use \sys\BaseObject;

//класс для работы с полем японского кроссворда, где оно разгадывается по одновременно по 2-м алгоритмам,
//т.е. каждая строка разгадывается сразу 2-мя методами с испольнованием чисел и блоков.
//Работает медленней чем FastField, но зато использует метод, который используется в юнит-тестах
class SlowField extends Field{

	private $_solveLines = [];
	
	public function addSolveLineByNumbers($line)
	{
		$this->_solveLines[$line->id] = $line;
	}
	
	public function addSolveLine($line)
	{
		$this->_solveLines[$line->id] = $line;
	}
	
	public function delSolveLine($line)
	{
		unset($this->_solveLines[$line->id]);
	}
	
	protected function solveLines():bool
	{
		$this->_isChange = false;
		while (count($this->_solveLines)) 
		{
			$lines = $this->_solveLines;
			$this->_solveLines = [];
			
			foreach($lines as $line)
			{	
                if (!$line->trySolveTest())
				{
					//$this->_solveLines = [];
                    return false;
				}
			}
			
		}
		return true;
	}
}