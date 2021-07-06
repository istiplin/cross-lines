<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из строк японского кроссворда
class Line extends BaseObject
{
    private $_ind;
    private $_field;
	
    private $_numbers;
    private $_cells;
    private $_groups;

    private $_unknownCount;
    
    private $_data;

    public function __construct($ind, array $numbers, string $cells, string $expectedResult=null, $isMirror = false)
    {
        $this->_ind = $ind;
        $this->_data = new LineData($numbers, $cells, $expectedResult, $isMirror);
        
        $this->init();
    }
	
    private function init()
    {
        if ($this->_numbers !== null AND $this->_cells !== null AND $this->_groups!==null)
            return;
            
        $this->_numbers = new Numbers($this->_data->getNumbersList(),$this);


        $this->_cells = new Cells($this->_data->getCellsStr(),$this);

        $this->_groups = $this->_cells->getGroups();
        $this->_numbers->setCells($this->_cells);
        $this->_numbers->setGroups($this->_groups);
    }
	
    public function getInd()
    {
        return $this->_ind;
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
        return $this->_data->getNumbersList();
    }

    private function setEmptyByNoNumbers(): bool
    {
        if ($this->_numbers->getCount() == 0){
            $this->_cells->setEmptyStates(0, $this->_cells->count-1);
        }
		
        if ($this->_unknownCount==0){
            return true;
        }

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
		
        if ($this->_unknownCount<0){
            throw new \Exception(" Error! state:$oldState->$state, line:{$this->_cells->ind}, pos:$pos. this->_unknownCount is bellow zero");
        }
    }

    public function solveByNumbers(): bool
    {
        try{
            $this->_numbers->solve();
        } catch(\Exception $e) {
            if (!$this->_field->isTest){
                echo $e->getMessage().'<br>';
            }
            return false;
        }
        return true;
    }
	
    public function solveByGroups(): bool
    {
        try{
            $this->_groups->solve();
        } catch(\Exception $e) {
            if (!$this->_field->isTest){
                echo $e->getMessage().'<br>';
            }
            return false;
        }
        return true;
    }

    public function trySolve($isView = false, $isDetail = false):bool
    {
        if ($this->_data->isSetError()){
            return !$this->_data->getIsError();
        }
        
        try{
            $this->solve($isView, $isDetail);
        } catch(\Exception $e) {
            if ($this->_field AND !$this->_field->isTest){
                echo $e->getMessage().'<br>';
            }
            $this->_data->setError($e->getMessage());
            return false;
        }
        return true;
    }
    
    public function getData(): LineData
    {
        return $this->_data;
    }

    public function solve($isView = false, $isDetail = false):bool
    {
        if ($this->setEmptyByNoNumbers()){
            $this->_data->setResult($this->_cells->getData());
            return true;
        }

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
        
        $this->_data->setResult($this->_cells->getData());
        return true;
    }
	
    public function getView($isViewChange=false)
    {
        return $this->_numbers->getLengthView().' '.$this->_cells->getData($isViewChange);
    }
    
    public function getCellsView()
    {
        return $this->_cells->getData();
    }
}