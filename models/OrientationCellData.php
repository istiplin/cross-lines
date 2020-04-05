<?php
namespace models;

use \sys\BaseObject;

//класс для работы с данными клетки определенной ориентации: горизонтальной или вертикальной
class OrientationCellData extends BaseObject
{
	private $_cell;
    private $_ind;	//номер индекса
    
    private $_line;
    private $_cells;
    
    private $_prev;
    private $_next;

    public $_group;
	
	public function __construct($ind,$cell)
	{
		$this->_cell = $cell;
		$this->_ind = $ind;
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
    
    public function setCells(Cells $cells)
    {
        $this->_cells = $cells;
        $this->_line = $cells->getLine();
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
        if ($prev!==null)
            $this->_prev->setNext($this);
    }

    public function setNext($next)
    {
        $this->_next = $next;
    }

}