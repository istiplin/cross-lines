<?php
namespace models;

use \sys\BaseObject;

//класс для работы с полем японского кроссворда, где оно разгадывается
class Field extends BaseObject
{
    public $name;

    public $_horLines = [];
    public $_vertLines = [];
    
    public $_width;
    public $_height;

    private $_cells = [];
	
    private $_cellsArr = [];
    private $_unknownCellsArr = [];
    private $_unknownCellsCount = 0;

    public $duration;
    public $beginTime;

    private $_numsList = [];
    private $_fieldCells;

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
	
        $this->_numsList = array_merge($horNums,$vertNums);
        
        $this->_fieldCells = new FieldCells($horNums,$vertNums,$cellsStrArr,$name);
    }

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
    
    public function getWidth()
    {
        return $this->_width;
    }
	
    public function sizeView()
    {
        return $this->_width.'X'.$this->_height;
    }
	
    //abstract protected function solveLines(): bool;

    public function timeIsUp(): bool
    {
        $this->duration = microtime(true) - $this->beginTime;
        if ($this->maxDuration)
            return $this->duration>=$this->maxDuration;
        return false;
    }
    
    protected function solveLines():bool
    {
        $linesCount = count($this->_numsList);
        $linesIds = [];
        for ($i = 0; $i < $linesCount; $i++) {
            $linesIds[$i] = $i;
        }
        
        $newLinesIds = [];
        $isChange = false;
        while(count($linesIds))
        {
            foreach ($linesIds as $lineId)
            {   
                $lineData = $this->_fieldCells->getLineOutput($lineId,$this->_numsList[$lineId]);
                if ($lineData->isError){
                    return false;
                }
                $newLinesIds = $lineData->getChangeIds($this->_height,$newLinesIds);
                if (count($newLinesIds)){
                    $isChange = true;
                }
                
                if ($isChange AND $this->timeIsUp()){
                    return !$this->isTest;
                }
            }
            
            $linesIds = $newLinesIds;
            $newLinesIds = [];
        }
        return true;
    }
    
    public function solve(): bool
    {
        return $this->_fieldCells->solve();
        
        $this->beginTime = microtime(true);

        /*
        if ($this->getUnknownCellsCount()==0)
        {
            $this->timeIsUp();
            return true;
        }
         * 
         */

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
        return $this->_fieldCells->getCells();
    }
	
    public function getHorNums()
    {
        return $this->_fieldCells->getHorNums();
    }

    public function getVertNums()
    {
        return $this->_fieldCells->getVertNums();
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