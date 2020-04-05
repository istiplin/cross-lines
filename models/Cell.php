<?php
namespace models;

use \sys\BaseObject;

//класс для работы с клетками, по которым строится рисунок
class Cell extends BaseObject
{
    const UNKNOWN_STATE = '0';
    const FULL_STATE = '1';
    const EMPTY_STATE = '2';

	private $_state;    //состояние клетки
	
	private $_field;
	
	private $_horOr;	//объект, в котором хранятся данные о горизонтальной ориентации текущего объекта клетки
	private $_vertOr;	//объект, в котором хранятся данные о вертикальной ориентации текущего объекта клетки

    private $_line;
    private $_cells;
    
    private $_prev;
    private $_next;

    public $_group;

    public function __construct($state,$x,$y=0,$field = null)
    {
		$this->_field = $field;
        $this->_state = $state;
		$this->_horOr = new OrientationCellData($x,$this);
		$this->_vertOr = new OrientationCellData($y,$this);
    }
	
	//возвращает наименование текущей ориентации
	public function getCurrOrName()
	{
		if ($this->_field===null)
			return '_horOr';
		return $this->_field->currOrName;
	}
	
    public function getState()
    {
        return $this->_state;
    }
    
    public function getFull()
    {
        return self::FULL_STATE;
    }
    
    public function getEmpty()
    {
        return self::EMPTY_STATE;
    }

    public function isUnknown():bool
    {
        return $this->_state==self::UNKNOWN_STATE;
    }

    public function isFull():bool
    {
        return $this->_state==self::FULL_STATE;
    }

    public function isEmpty():bool
    {
        return $this->_state==self::EMPTY_STATE;
    }
	
    public function setFull()
    {
        if ($this->isEmpty())
            throw new \Exception($this->cells->getView().' error in line:'.$this->line->ind.' pos '.$this->ind.' is empty instead full');
        $this->setState(self::FULL_STATE);
    }

    public function setEmpty()
    {
        if ($this->isFull())
            throw new \Exception($this->cells->getView().' error in line:'.$this->line->ind.' pos '.$this->ind.' is full instead empty');
        $this->setState(self::EMPTY_STATE);
    }
	
    private function setState($state)
    {
        if ($this->isUnknown())
        {
			$this->_state = $state;
			$this->setIsChange();
        }
    }
	
	private function setIsChange()
	{
		$line = $this->{$this->getCurrOrName()}->getLine();
		$ind = $this->ind;
		
		$line->isChange = true;
		$this->getCells()->decrUnknownCount($ind);
		if ($line->crossLines)
		{
			$line->crossLines[$ind]->isChange = true;
			$line->crossLines[$ind]->getCells()->decrUnknownCount($line->ind);
		}
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

	
	
	public function getInd()
	{
		return $this->{$this->getCurrOrName()}->getInd();
	}
	
    public function getDist($pos): int
    {
		return $this->{$this->getCurrOrName()}->getDist($pos);
    }
	
    public function setCells(Cells $cells)
    {
		$this->{$this->getCurrOrName()}->setCells($cells);
    }
	
	public function getCells()
	{
		return $this->{$this->getCurrOrName()}->getCells();
	}
	
    public function getPrev(): ?self
    {
        return $this->{$this->getCurrOrName()}->getPrev();
    }

    public function getNext(): ?self
    {
        return $this->{$this->getCurrOrName()}->getNext();
    }

    public function setPrev($value)
    {
		$this->{$this->getCurrOrName()}->setPrev($value);
    }

    public function setNext($value)
    {
        $this->{$this->getCurrOrName()}->setNext($value);
    }
	
}