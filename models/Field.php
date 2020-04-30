<?php
namespace models;

use \sys\BaseObject;

//класс для работы с полем японского кроссворда, где оно разгадывается
abstract class Field extends BaseObject
{
    public $name;

    public $_horLines = [];
    public $_vertLines = [];
    
    public $_width;
    public $_height;
	
	
    private $_cellsArr = [];
	private $_unknownCellsArr = [];
	private $_unknownCellsCount = 0;
	
	public $duration;
	public $beginTime;
	
	private $_horNums;
	private $_vertNums;
	
	private $_solveLinesByNumbers = [];
	private $_solveLinesByGroups = [];
	
	private $_solveLines = [];
	
	public $isTest = false;
	public $testCells=[];

	public $t = [];
	
	protected $_isChange = true;
	
	public $maxDuration = 0;

    public function __construct($horNums,$vertNums,$cellsStrArr=null,$name=null)
    {
        $this->name = $name;
        
        $this->_width = count($vertNums);
        $this->_height = count($horNums);
		
		if ($cellsStrArr)
			$cellsStrArr = $this->trimCellsStrArr($cellsStrArr);
			
        for ($i=0; $i<$this->_height; $i++)
		{
			$horCells = $this->getHorCells($i,$cellsStrArr);
			$line = new Line($i,$horNums[$i],$horCells,true,$this);
            $this->_horLines[$i] = $line;
			$this->addSolveLine($line);
		}

        for ($i=0; $i<$this->_width; $i++)
		{
			$vertCells = $this->getVertCells($i,$cellsStrArr);
			$line = new Line($i,$vertNums[$i],$vertCells,false,$this);
            $this->_vertLines[$i] = $line;
			$this->addSolveLine($line);
		}
    }
	
	private function trimCellsStrArr($value)
	{
		for ($i=0; $i<$this->_height; $i++)
		{
			$value[$i] = str_replace(' ','',$value[$i]);
		}
		return $value;
	}
	
	private function getHorCells($ind,$cellsStrArr=null): string
	{
		if ($cellsStrArr===null)
			return str_repeat(Cell::UNKNOWN_STATE, $this->_width);
		else
			return $cellsStrArr[$ind];
	}
	
	private function getVertCells($ind,$cellsStrArr=null): string
	{
		if ($cellsStrArr===null)
			return str_repeat(Cell::UNKNOWN_STATE, $this->_height);
		else
		{
			$str = '';
			for ($i=0; $i<$this->_height; $i++)
				$str.=$cellsStrArr[$i][$ind];
			return $str;
		}
	}
	
	abstract public function addSolveLine($line);
	abstract public function addSolveLineByNumbers($line);
	
	public function addUnknownCell($cell)
	{
		if ($cell->isUnknown())
		{
			$this->_unknownCellsArr[$cell->getId()]=$cell;
			$this->_unknownCellsCount++;
		}
	}
	
	public function deleteUnknownCell($ind)
	{
		unset($this->_unknownCellsArr[$ind]);
		$this->_unknownCellsCount--;
	}
	
	public function getUnknownCell($ind): Cell
	{
		return $this->_unknownCellsArr[$ind];
	}
	
	public function getUnknownCellsCount(): int
	{
		return $this->_unknownCellsCount;
	}
	
	private function createCell($x,$y,Line $line):Cell
	{
		if (isset($this->_cellsArr[$y][$x]))
			return $this->_cellsArr[$y][$x];
			
		$cell = new Cell($x,$y,$this);
		
		$this->_cellsArr[$y][$x] = $cell;
		$this->addUnknownCell($cell);
		
		return $this->_cellsArr[$y][$x] = $cell;
	}
	
    public function getCell($ind,Line $line): Cell
    {
        if ($line->isHorizontal)
			return $this->createCell($ind,$line->ind,$line);
		return $this->createCell($line->ind,$ind,$line);
    }
    
	public function getWidth()
	{
		return $this->_width;
	}
	
    public function sizeView()
    {
        return $this->_width.'X'.$this->_height;
    }
	
	
	abstract protected function solveLines(): bool;
	
	public function timeIsUp(): bool
	{
		$this->duration = microtime(true) - $this->beginTime;
		if ($this->maxDuration)
			return $this->duration>=$this->maxDuration;
		return false;
	}
	
	public function solve(): bool
	{
		$this->beginTime = microtime(true);
		
		if ($this->getUnknownCellsCount()==0)
		{
			$this->timeIsUp();
			return true;
		}

        while ($this->_isChange) {
			if ($this->timeIsUp())
				return !$this->isTest;
		
			if (!$this->solveLines())
				return false;
			
            $this->trySolve();
        }
		
		$this->timeIsUp();
		return true;
	}

	private function clearTestCells()
	{
		foreach($this->testCells as $cell)
			$cell->setUnknown();
		$this->isTest = false;
		
		$this->testCells = [];
	}
	
	private function trySolveLines($trialCell):bool
	{
		$this->isTest = true;
		$trialCell->setFull();
		$this->_isChange = true;
		if (!$this->solveLines())
		{
			$this->clearTestCells();
			if ($this->timeIsUp())
			{
				$trialCell->setUnknown();
				return true;
			}
			$trialCell->setEmpty();
			$this->_isChange = true;
			
			return true;
		}
		else
		{
			if ($this->_unknownCellsCount==0)
				return true;
			$this->clearTestCells();
		}
		return false;
	}
	
	//разгадывает кроссворд методом проб и ошибок
	private function trySolve()
	{
		foreach($this->_unknownCellsArr as $cell)
		{
			if ($this->timeIsUp())
				return;
			if (
				$cell->leftIsEmpty() AND $cell->upIsEmpty()
				OR $cell->leftIsEmpty() AND $cell->downIsEmpty()
				OR $cell->rightIsEmpty() AND $cell->upIsEmpty() 
				OR $cell->rightIsEmpty() AND $cell->downIsEmpty()
				OR $cell->leftIsEmpty() OR $cell->rightIsEmpty() OR $cell->upIsEmpty() OR $cell->downIsEmpty()
			)
			{
				if ($this->trySolveLines($cell))
					return;
			}
		}
	}
	
	public function getCells()
	{
		$cells=[];
        for ($y=0; $y<$this->_height; $y++)
        {
            for($x=0; $x<$this->_width; $x++)
                $cells[$y][$x]=$this->_cellsArr[$y][$x]->state;
        }
		return $cells;
	}
	
	public function getHorNums()
	{
		if ($this->_horNums!==null)
			return $this->_horNums;
			
		$this->_horNums = [];
		for ($i=0; $i<$this->_height; $i++)
			$this->_horNums[] = $this->_horLines[$i]->getNumbers()->getLengthArray();
		
		return $this->_horNums;
	}
	
	public function getVertNums()
	{
		if ($this->_vertNums!==null)
			return $this->_vertNums;
			
		$this->_vertNums = [];
		for ($i=0; $i<$this->_width; $i++)
			$this->_vertNums[] = $this->_vertLines[$i]->getNumbers()->getLengthArray();
		
		return $this->_vertNums;
	}
	
    public function getView()
    {
        $view = '<br>';
        for ($y=0; $y<$this->_height; $y++)
        {
            for($x=0; $x<$this->_width; $x++)
                $view.=$this->_cellsArr[$y][$x]->state;
            $view.='<br>';
        }
		$view.='Длительность: '.$this->duration.' сек.<br>';
        return $view;
    }
	
	public function unknownCellsArrView()
	{
		echo '(x,y):<br>';
		foreach($this->_unknownCellsArr as $unknownCell)
		{
			echo "({$unknownCell->getX()},{$unknownCell->getY()})<br>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;BY FULL:{$unknownCell->unknownCountByFull};<br>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;BY EMPTY:{$unknownCell->unknownCountByEmpty};<br>";
		}
			
		echo '<br>';
	}

}