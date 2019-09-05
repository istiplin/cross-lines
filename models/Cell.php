<?php
namespace models;

use \sys\BaseObject;

//класс для работы с клетками, по которым строится рисунок
class Cell extends BaseObject
{
    const UNKNOWN_STATE = '0';
    const FULL_STATE = '1';
    const EMPTY_STATE = '2';

    public $ind;	//номер индекса
    
    private $_line;
    private $_cells;
    
    private $_state;    //состояние клетки
    
    private $_prev;
    private $_next;

    public $group;

    public function __construct($state)
    {
        $this->_state = $state;
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
    
    public function nextIsFull():bool
    {
        return $this->_next!==null AND $this->_next->isFull();
    }

    public function prevIsFull():bool
    {
        return $this->_prev!==null AND $this->_prev->isFull();
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
        if ($prev!==null)
            $this->_prev->next = $this;
    }

    public function setNext($next)
    {
        $this->_next = $next;
    }
    
    public function setCells(Cells $cells)
    {
        $this->_cells = $cells;
        $this->_line = $cells->line;
    }
    
    private function setState($state)
    {
        if ($this->isUnknown())
        {
            $this->_cells->decrUnknownCount($this->ind);
            $this->_state = $state;
            $this->_line->isChange = true;
            if ($this->_line->crossLines)
            {
                $this->_line->crossLines[$this->ind]->isChange = true;
                $this->_line->crossLines[$this->ind]->cells->decrUnknownCount($this->_line->ind);
            }
        }
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


    public function getDist($pos)
    {
        if ($this->ind<=$pos)
            return $pos - $this->ind + 1;
        if ($this->ind>$pos)
            return $this->ind - $pos + 1;
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