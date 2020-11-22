<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из строк японского кроссворда
class Line extends BaseObject
{
    private $_field;
	
    private $_numbers;
    private $_cells;
    private $_groups;

    private $_unknownCount;
    
    private $_output;

    public function __construct($ind, array $numbers, string $cells, string $expectedResult=null, $isMirror = false)
    {
        $this->_output = new LineData($ind, $numbers, $cells, $expectedResult, $isMirror);
        
        //$this->init();
    }
	
    private function init()
    {
        if ($this->_numbers !== null AND $this->_cells !== null AND $this->_groups!==null)
            return;
            
        $this->_numbers = new Numbers($this->_output->getNumbersList(),$this);


        $this->_cells = new Cells($this->_output->getCellsStr(),$this);

        $this->_groups = $this->_cells->getGroups();
        $this->_numbers->setCells($this->_cells);
        $this->_numbers->setGroups($this->_groups);
    }
    
    /*
    private function uninit()
    {
        $this->_numbers->setGroups(null);
        $this->_numbers->unsetCells();
        $this->_groups = null;

        $this->_cells = null;
        $this->_numbers = null;
    }
     * 
     */

    
    //используется для юнит-тестов
    /*
    public function __clone()
    {	
        $this->_groups = null;

        $this->_field = null;

        $this->_numbers = new Numbers($this->_output->getNumbersList(),$this);
        $this->_cells = new Cells($this->_cells->getData(),$this);

        $this->_groups = $this->_cells->getGroups();
        $this->_numbers->setCells($this->_cells);
        $this->_numbers->setGroups($this->_groups);
    }
     * 
     */
	
    public function getInd()
    {
        return $this->_output->ind;
    }
    
    public function getField(): ?Field
    {
        return $this->_field;
    }

    public function getCells(): Cells
    {
        return $this->_cells;
    }

    public function getNumbers(): Numbers
    {
        return $this->_numbers;
    }

    public function getNumbersList()
    {
        return $this->_output->getNumbersList();
    }

    private function setEmptyByNoNumbers(): bool
    {
        if ($this->_numbers->getCount() == 0)
            $this->_cells->setEmptyStates(0, $this->_cells->count-1);
		
        if ($this->_unknownCount==0)
            return true;

        return false;
    }
	
    public function setUnknownCount($value)
    {
        $this->_unknownCount = $value;
    }
	
    public function getUnknownCount(): int
    {
        return $this->_unknownCount;
    }
    
    public function incUnknownCount()
    {
        $this->_unknownCount++;

        if ($this->_unknownCount>$this->_cells->getCount())
            throw new \Exception(" Error! this->_unknownCount is more than count of cells");
    }
	
    public function decrUnknownCount($pos,$oldState,$state)
    {
        $this->_unknownCount--;
		
        if ($this->_unknownCount<0)
        {
            throw new \Exception(" Error! state:$oldState->$state, line:{$this->_cells->ind}, pos:$pos. this->_unknownCount is bellow zero");
        }
    }
	
    public function solveByNumbers(): bool
    {
        try{
            $this->_numbers->solve();
        }
        catch(\Exception $e)
        {
            if (!$this->_field->isTest)
                echo $e->getMessage().'<br>';
            return false;
        }

        return true;
    }
	
    public function solveByGroups(): bool
    {
        try{
            $this->_groups->solve();
        }
        catch(\Exception $e)
        {
            if (!$this->_field->isTest)
                echo $e->getMessage().'<br>';
            return false;
        }

        return true;
    }

    public function trySolveTest($isView = false, $isDetail = false):bool
    {
        if ($this->_output->isError !== null)
            return !$this->_output->isError;
        
        try{
            $this->init();
            $this->solveTest($isView, $isDetail);
        }
        catch(\Exception $e)
        {
            if ($this->_field AND !$this->_field->isTest){
                echo $e->getMessage().'<br>';
            }
            $this->_output->setError($e->getMessage());
            return false;
        }
        $this->_output->setSuccess($this->_cells->getData());

        return true;
    }
    
    public function getOutput()
    {
        $this->trySolveTest();
        return $this->_output;
    }
	
    public function solveTest($isView = false, $isDetail = false):bool
    {
        if ($this->setEmptyByNoNumbers())
            return true;

        $this->_groups->resetList();
        $this->_numbers->clearBounds();

        $this->_numbers->setBounds($isDetail);
        $this->_groups->setGroupNumbers($isDetail);

        $this->_numbers->setStateCells($isView);
        $this->_groups->setStateCells($isView);

        $this->_groups->resetList();
        $this->_groups->setGroupNumbers($isDetail);

        $this->_groups->setStateCells($isView);
        $this->_numbers->setStateCells($isView);
		
		
        /*
        $this->_groups->resetList();
        $this->_numbers->clearBounds();

        $this->_numbers->setBounds($isDetail);
        $this->_groups->setGroupNumbers($isDetail);
        $this->_groups->setGroupNumbers($isDetail);

        $this->_numbers->setStateCells($isView);
        $this->_groups->setStateCells($isView);
        */

        /*
        //потом пытаемся разгадать строку, рассматривая каждое число
        $this->_numbers->solve($isView, $isDetail);

        //затем пытаемся разгадать строку, рассматривая каждый блок однотипных клеток
        $this->_groups->solve($isView, $isDetail);

        //потом пытаемся разгадать строку, рассматривая каждое число
        $this->_numbers->solve($isView, $isDetail);
        */

        /*
        if ($this->_field)
        {
            $this->_field->delSolveLine($this);
            //$this->_groups->unsetList();
            //$this->uninit();
        }
        else
        {
            $this->_groups->solveByClone();
        }
         * 
         */

        return true;
    }
	
    public function getView($isViewChange=false)
    {
        $this->init();
        return $this->_numbers->getLengthView().' '.$this->_cells->getData($isViewChange);
    }
    
    public function getCellsView()
    {
        $this->init();
        return $this->_cells->getData();
    }
}