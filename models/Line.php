<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из линий японского кроссворда
class Line extends BaseObject
{
    public $isChange = true;

    private $_numbers;
    private $_cells;
    private $_groups;

    private $_ind;
    private $_isMirror = false;

    public function __construct($ind,$numbers,$cells,$isMirror=false)
    {
        $this->_ind = $ind;
        if ($isMirror)
        {
            $this->_isMirror = $isMirror;
            $numbers = array_reverse($numbers);
            $cells = strrev($cells);
        }
        $this->_cells = new Cells($cells,$this);
        $this->_numbers = new Numbers($numbers,$this);
        $this->_groups = new Groups($this->_cells);
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
    
    public function resolve()
    {
        //$this->groups->resolve();
        //return;
        
        $this->setEmptyByNoNumbers();
        while ($this->isChange)
        {
            $this->isChange = false;
            if ($this->cells->unknownCount==0)
                break;
            
            $this->numbers->resolve();
            $this->groups->resolve();
        }
    }
    
    public function getCellsView()
    {
        $cellsView = $this->cells->getView();
        if ($this->_isMirror)
            $cellsView = strrev($cellsView);
        return $cellsView;
    }
}