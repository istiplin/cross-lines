<?php
namespace models;

use \sys\BaseObject;

//Класс, в котором хранятся данные клетки, являющийся объектом класса Cell. 
//Каждая клетка, являющаяся объектом класса Cell, имеет 2 объекта текущего класса.
//Каждый объект текущего класса соответствует своей ориентации: горизонтальная или вертикальная
class CellData extends BaseObject
{
    private $_cell;
    private $_ind;	//номер индекса
    
    private $_cells;
    private $_line;
    private $_field;
    private $_numbers;
	 
    
    private $_prev;
    private $_next;

    private $_group;
	
    public function __construct($ind,$cell,$cells)
    {
        $this->_ind = $ind;
        $this->setCells($cells);
        $this->setCell($cell);
    }

    public function setCell(Cell $cell)
    {
        $this->_cell = $cell;
        $cell->setCellData($this);
    }

    public function getLine()
    {
        return $this->_line;
    }

    public function getInd()
    {
        return $this->_ind;
    }
	
    public function getDist($pos): int
    {
        if ($this->_ind<=$pos)
            return $pos - $this->_ind + 1;
        if ($this->_ind>$pos)
            return $this->_ind - $pos + 1;
    }
    
    private function setCells(Cells $cells)
    {
        $this->_cells = $cells;
        $this->_line = $cells->getLine();
        $this->_field = $this->_line->getField();
        $this->_numbers = $this->_line->getNumbers();
    }

    public function getCells()
    {
            return $this->_cells;
    }
	
    public function getPrev(): ?Cell
    {
		if ($this->_prev === null)
			return null;
        return $this->_prev->_cell;
    }

    public function getNext(): ?Cell
    {
		if ($this->_next === null)
			return null;
        return $this->_next->_cell;
    }

    public function setPrev($prev)
    {
        $this->_prev = $prev;
        if ($this->_prev!==null)
            $prev->setNext($this);
    }
	
	public function unsetPrev()
	{
		if ($this->_prev!==null)
			$this->_prev->setNext(null);
		$this->_prev = null;
	}

	
    public function setNext($next)
    {
        $this->_next = $next;
    }
	
    public function getState()
    {
            return $this->_cell->getState();
    }
	
    public function setFull()
    {
        $this->_cell->setFull();
    }

    public function setEmpty()
    {
        $this->_cell->setEmpty();
    }
	
    public function setState($value)
    {
            $this->_cell->setState($value);
    }

    public function setFieldInd($value)
    {
            $this->_cell->fieldInd = $value;
    }

    public function getFieldInd()
    {
            return $this->_cell->fieldInd;
    }

    public function isUnknown($state=null):bool
    {
            return $this->_cell->isUnknown($state);
    }
	
    public function isFull($state=null):bool
    {
		return $this->_cell->isFull($state);
    }

    public function isEmpty($state=null):bool
    {
		return $this->_cell->isEmpty($state);
    }
	
	
    public function nextIsEmpty():bool
    {
        return $this->_next===null OR $this->_next->isEmpty();
    }

    public function prevIsEmpty():bool
    {
        return $this->_prev===null OR $this->_prev->isEmpty();
    }
    
    public function nextIsFull():bool
    {
        return $this->_next!==null AND $this->_next->isFull();
    }

    public function prevIsFull():bool
    {
        return $this->_prev!==null AND $this->_prev->isFull();
    }
	
	
	public function setGroup($value)
	{
		$this->_group = $value;
	}
	
	public function getGroup(): EmptyGroup
	{
		return $this->_group;
	}

    public function getGroupEnd():int
    {
        return $this->_group->end;
    }

    public function getGroupStart():int
    {
        return $this->_group->start;
    }

    public function getGroupLength():int
    {
        return $this->_group->length;
    }

    public function getNextGroupIsEmpty():bool
    {
        return $this->_group->nextIsEmpty();
    }

    public function getNextGroupIsFull():bool
    {
        return $this->_group->nextIsFull();
    }

    public function getPrevGroupIsEmpty():bool
    {
        return $this->_group->prevIsEmpty();
    }

    public function getPrevGroupIsFull():bool
    {
        return $this->_group->prevIsFull();
    }
}