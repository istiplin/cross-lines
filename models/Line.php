<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из линий японского кроссворда
class Line extends BaseObject
{
    public $isChange = true;
    
    public $crossLines=null;
    public $isHorizontal;
    public $field;
    
    private $_numbers;
    private $_cells;
    private $_groups;

    private $_ind;
    private $_isMirror = false;

    public function __construct($ind,$numbers,string $cellsStr,bool $isHorizontal,$field,$isMirror=false)
    {
        $this->_ind = $ind;
        $this->_isMirror = $isMirror;
        if ($this->_isMirror)
        {
            $numbers = array_reverse($numbers);
            $cellsStr = strrev($cellsStr);
        }
        $this->isHorizontal = $isHorizontal;
        $this->field = $field;

        $this->_cells = new Cells($this,$cellsStr);
        $this->_numbers = new Numbers($numbers,$this);
    }
    
    public function getInd(): int
    {
        return $this->_ind;
    }

    public function getNumbers(): Numbers
    {
        return $this->_numbers;
    }
    
    public function getCells(): Cells
    {
        return $this->_cells;
    }

    public function getGroups(): Groups
    {
        return $this->_groups;
    }

    private function setEmptyByNoNumbers()
    {
        if ($this->numbers->count == 0)
            $this->cells->setEmptyStates(0, $this->cells->count-1);
    }
    
    public function resolve($withView=false,$withDetail=false)
    {
        $this->_cells->changeAttrs();
        $this->_groups = new Groups($this->_cells);
        
        $this->_numbers->resetBounds();
        
        if ($withView)
            $this->cells->view();
        
        if ($withDetail)
            $this->numbers->printBounds();
        
        $this->setEmptyByNoNumbers();
        while ($this->isChange)
        {
            $this->isChange = false;

            if ($this->cells->unknownCount>0) {
                
                $this->numbers->resolve();
                
                if ($withDetail)
                {
                    $this->cells->view();
                    $this->numbers->printBounds();
                }
            }

            if ($this->cells->unknownCount>0) {
                if ($withDetail)
                    $this->cells->view();
                
                $this->groups->resolve();
                
                if ($withDetail)
                    $this->groups->view();
            }
        }
        
        if ($withView)
            $this->cells->view();
    }
    
    public function getCellsView()
    {
        $cellsView = $this->cells->getView();
        if ($this->_isMirror)
            $cellsView = strrev($cellsView);
        return $cellsView;
    }
}