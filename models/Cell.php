<?php
namespace models;

use \sys\BaseObject;

//класс для работы с клетками, по которым строится рисунок
class Cell extends BaseObject
{
    const UNKNOWN_STATE = 0;
    const FULL_STATE = 1;
    const EMPTY_STATE = 2;

    private $_line;
    private $_cells;
    
    private $_state = 0;		//состояние клетки
    private $_ind;				//номер индекса
    private $_prev;
    private $_next;

    public $group;

    public function __construct($cells,$state,$ind,$prev=null)
    {
        $this->_cells = $cells;
        $this->_line = $cells->line;
        $this->_state = $state;
        $this->_ind = $ind;

        if ($prev!==null)
        {
            //устанавливаем ссылку на предыдущую клетку
            $this->prev = $prev;

            //в предыдущей клетке устанавливаем ссылку на текущую клетку
            $this->prev->next = $this;
        }
    }

    public function getLine(): Line
    {
        return $this->_line;
    }
    
    public function getCells(): Cells
    {
        return $this->_cells;
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

    public function nextIsEmpty():bool
    {
        return $this->_next===null OR $this->_next->isEmpty();
    }

    public function prevIsEmpty():bool
    {
        return $this->_prev===null OR $this->_prev->isEmpty();
    }

    public function getInd(): int
    {
        return $this->_ind;
    }

    public function getPrev(): ?self
    {
        return $this->_prev;
    }

    public function getNext(): ?self
    {
        return $this->_next;
    }

    public function setPrev($prev)
    {
        $this->_prev = $prev;
    }

    public function setNext($next)
    {
        $this->_next = $next;
    }

    public function setFull()
    {
        if ($this->isEmpty())
            throw new \Exception($this->cells->getView().' error in line:'.$this->line->ind.' pos '.$this->_ind.' is empty instead full');

        if ($this->isUnknown())
        {
            $this->_cells->decrUnknownCount($this->_ind);
            $this->_line->isChange = true;
            $this->_state = self::FULL_STATE;    
        }
    }

    public function setEmpty()
    {
        if ($this->isFull())
            throw new \Exception($this->cells->getView().' error in line:'.$this->line->ind.' pos '.$this->_ind.' is full instead empty');

        if ($this->isUnknown())
        {
            $this->_cells->decrUnknownCount($this->_ind);
            $this->_line->isChange = true;
            $this->_state = self::EMPTY_STATE;
        }
    }

    public function getDist($pos)
    {
        if ($this->_ind<=$pos)
            return $pos - $this->_ind + 1;
        if ($this->_ind>$pos)
            return $this->_ind - $pos + 1;
    }

    public function getGroupEnd():int
    {
        return $this->group->end;
    }

    public function getGroupStart():int
    {
        return $this->group->start;
    }

    public function getGroupLength():int
    {
        return $this->group->length;
    }

    public function getNextGroupIsEmpty():bool
    {
        return $this->group->nextIsEmpty();
    }

    public function getNextGroupIsFull():bool
    {
        return $this->group->nextIsFull();
    }

    public function getPrevGroupIsEmpty():bool
    {
        return $this->group->prevIsEmpty();
    }

    public function getPrevGroupIsFull():bool
    {
        return $this->group->prevIsFull();
    }

}