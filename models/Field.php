<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из линий японского кроссворда
class Field extends BaseObject
{
    public $name;

    private $_horLines = [];
    private $_vertLines = [];
    
    private $_width;
    private $_height;
	
	private $_isChange = true;
    private $_cellsArr = [];
	private $_unknownCellsArr = [];
	private $_unknownCellsCount = 0;
	
	public $duration;
	public $beginTime;

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
            $this->_horLines[$i] = new Line($i,$horNums[$i],$horCells,true,$this);
		}

        for ($i=0; $i<$this->_width; $i++)
		{
			$vertCells = $this->getVertCells($i,$cellsStrArr);
            $this->_vertLines[$i] = new Line($i,$vertNums[$i],$vertCells,false,$this);
		}
        
        for ($i=0; $i<$this->_height; $i++)
            $this->_horLines[$i]->crossLines = $this->_vertLines;
        
        for ($i=0; $i<$this->_width; $i++)
            $this->_vertLines[$i]->crossLines = $this->_horLines;
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
	
	public function __clone()
	{
	    $this->_isChange = true;
		$this->_cellsArr = [];
		$this->_unknownCellsArr = [];
		$this->_unknownCellsCount = 0;
		
        for ($i=0; $i<$this->_height; $i++)
		{
			$numbersList = $this->_horLines[$i]->getNumbersList();
			$cellsStr = $this->_horLines[$i]->getCells()->getData();
            $this->_horLines[$i] = new Line($i,$numbersList,$cellsStr,true,$this);
		}
        
        for ($i=0; $i<$this->_width; $i++)
		{
			$numbersList = $this->_vertLines[$i]->getNumbersList();
			$cellsStr = $this->_vertLines[$i]->getCells()->getData();
            $this->_vertLines[$i] = new Line($i,$numbersList,$cellsStr,false,$this);
		}
        
        for ($i=0; $i<$this->_height; $i++)
            $this->_horLines[$i]->crossLines = $this->_vertLines;
        
        for ($i=0; $i<$this->_width; $i++)
            $this->_vertLines[$i]->crossLines = $this->_horLines;
			
	}
    
	private function getCellInd(int $x,int $y)
	{
		return $y*$this->_width+$x;
	}
	
	private function getCellCoord(int $ind)
	{
		$x = $ind%$this->_width;
		$y = (int)($ind/$this->_width);
		return [$x,$y];
	}
	
	private function addUnknownCell($ind,$cell)
	{
		$this->_unknownCellsArr[$ind]=$cell;
		$this->_unknownCellsCount++;
		$cell->fieldInd = $ind;
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
			
		$cell = new Cell($this);
		$this->_cellsArr[$y][$x] = $cell;
		if ($cell->isUnknown())
		{
			$cellInd = $this->getCellInd($x,$y);
			$this->addUnknownCell($cellInd,$cell);
		}
		return $this->_cellsArr[$y][$x] = $cell;
	}
	
    public function getCell($ind,Line $line): Cell
    {
        if ($line->isHorizontal)
			return $this->createCell($ind,$line->ind,$line);
		return $this->createCell($line->ind,$ind,$line);
    }
    
    public function sizeView()
    {
        return $this->_width.'X'.$this->_height;
    }

    public function resolveByNumbers(): bool
    {
        $isChange = true;
        while($isChange)
        {
            $isChange = false;

            for ($i=0; $i<$this->_height; $i++)
            {
                if (!$this->_horLines[$i]->isChangeByNumbers)
                    continue;

                $this->_isChange = true;
                $isChange = true;
                if (!$this->_horLines[$i]->resolveByNumbers())
                    return false;
            }
			
            for ($i=0; $i<$this->_width; $i++)
            {
                if (!$this->_vertLines[$i]->isChangeByNumbers)
                    continue;

                $this->_isChange = true;
                $isChange = true;
                if (!$this->_vertLines[$i]->resolveByNumbers())
                    return false;
            }
        }
        return true;
    }

    public function resolveByGroups(): bool
    {
        $isChange = true;
        while($isChange)
        {
            $isChange = false;

            for ($i=0; $i<$this->_height; $i++)
            {
                if (!$this->_horLines[$i]->isChangeByGroups)
                    continue;

                $this->_isChange = true;
                $isChange = true;
                if (!$this->_horLines[$i]->resolveByGroups())
                    return false;
            }

            for ($i=0; $i<$this->_width; $i++)
            {
                if (!$this->_vertLines[$i]->isChangeByGroups)
                    continue;

                $this->_isChange = true;
                $isChange = true;
                if (!$this->_vertLines[$i]->resolveByGroups())
                    return false;
            }
        }
        return true;
    }

	public function resolve($isClone=true): bool
	{
		$this->beginTime = microtime(true);
		
		if ($this->getUnknownCellsCount()==0)
		{
			$this->duration = microtime(true) - $this->beginTime;
			return true;
		}

        while ($this->_isChange) {
            while ($this->_isChange) {

                $this->_isChange = false;

                if (!$this->resolveByNumbers())
                    return false;

                if ($this->getUnknownCellsCount() == 0) {
                    $this->duration = microtime(true) - $this->beginTime;
                    return true;
                }

                if (!$this->resolveByGroups())
                    return false;

                if ($this->getUnknownCellsCount() == 0) {
                    $this->duration = microtime(true) - $this->beginTime;
                    return true;
                }
            }
            if ($isClone)
                $this->resolveByClone();
        }
		
		
		$this->duration = microtime(true) - $this->beginTime;
		return true;
	}

	public function resolveByClone(): bool
	{
		$isResolve = false;
		foreach($this->_unknownCellsArr as $ind=>$unknownCell)
		{
			//if (microtime(true) - $this->beginTime>5)
			//	break;
			
			$field = clone $this;
			$field->getUnknownCell($ind)->setFull();
			if (!$field->resolve(false))
			{
				$this->_unknownCellsArr[$ind]->setEmpty();
                $this->_isChange = true;
				return $isResolve = true;
				continue;
			}
			else
				$this->_unknownCellsArr[$ind]->unknownCountByFull = $field->getUnknownCellsCount();
			
			$field = clone $this;
			$field->getUnknownCell($ind)->setEmpty();
			if (!$field->resolve(false))
			{
				$this->_unknownCellsArr[$ind]->setFull();
                $this->_isChange = true;
				return $isResolve = true;
				continue;
			}
			else
				$this->_unknownCellsArr[$ind]->unknownCountByEmpty = $field->getUnknownCellsCount();
			
		}
		return $isResolve;
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
		foreach($this->_unknownCellsArr as $ind=>$unknownCell)
		{
			$coord = $this->getCellCoord($ind);
			echo "({$coord[0]},{$coord[1]})<br>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;BY FULL:{$unknownCell->unknownCountByFull};<br>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;BY EMPTY:{$unknownCell->unknownCountByEmpty};<br>";
		}
			
		echo '<br>';
	}

}