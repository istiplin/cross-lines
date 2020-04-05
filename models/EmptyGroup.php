<?php
namespace models;

use \sys\BaseObject;

//класс для работы с группой соседних клеток, помеченные крестиками
class EmptyGroup extends BaseObject
{
    protected $_state = Cell::EMPTY_STATE;
    protected $_groups;

    protected $_start;
    protected $_length;
    protected $_end;
    protected $_ind;

    protected $_prev;
    protected $_next;

    public static function initial($groups,string $state,$start,$ind,$prev=null)
    {
        if ($state===Cell::UNKNOWN_STATE)
            return new UnknownGroup($groups,$start,$ind,$prev);
        if ($state===Cell::FULL_STATE)
            return new FullGroup($groups,$start,$ind,$prev);
        if ($state===Cell::EMPTY_STATE)
            return new self($groups,$start,$ind,$prev);
        throw new \Exception("state=$state is not 'Unknown','FULL' or 'Empty' ");
    }

    public function __construct($groups,$start,$ind,$prev=null)
    {
        $this->_groups = $groups;
        $this->_start = $start;
        $this->_ind = $ind;

        if ($prev!==null)
        {
            //устанавливаем ссылку на предыдую группу
            $this->setPrev($prev);

            //в предыдущей группе устанавливаем ссылку на текущую группу
            $this->getPrev()->setNext($this);
        }
    }

    public function getStart(): int
    {
        return $this->_start;
    }

    public function setEnd(int $value)
    {
        if ($this->_end!==null)
            throw new \Exception('$this->_end is not null');
        $this->_end = $value;
        $this->_length = $this->_end - $this->_start + 1;
    }

    public function getEnd(): int
    {
        return $this->_end;
    }

    public function getInd()
    {
        return $this->_ind;
    }

    public function getCells(): Cells
    {
        return $this->_groups->cells;
    }
    
    public function getNumbers(): Numbers
    {
        return $this->line->numbers;
    }

    public function getLine(): Line
    {
        return $this->_groups->cells->line;
    }

    public function setPrev($prev)
    {
        if ($prev->getState()==$this->getState())
            throw new \Exception('Prev group state='.$this->getState().' is same current group state');
        $this->_prev = $prev;
    }
    
    public function getPrev(): ?self
    {
        return $this->_prev;
    }

    public function setNext($next)
    {
        if ($next->getState()==$this->getState())
            throw new \Exception('Next group state='.$this->getState().' is same current group state');
        $this->_next = $next;
    }
    
    public function getNext(): ?self
    {
        return $this->_next;
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

    public function nextIsUnknown():bool
    {
        return $this->_next!==null AND $this->_next->isUnknown();
    }

    public function prevIsUnknown():bool
    {
        return $this->_prev!==null AND $this->_prev->isUnknown();
    }
    
    public function nextHasEmpty():bool
    {
        if ($this->_next===null)
            return true;
        return $this->_next->getHasEmpty();
    }
    
    public function prevHasEmpty():bool
    {
        if ($this->_prev===null)
            return true;
        return $this->_prev->getHasEmpty();
    }
    
    public function getState()
    {
        return $this->_state;
    }

    public function getLength()
    {
        return $this->_length;
    }

    public function isUnknown():bool
    {
        return false;
    }

    public function isFull():bool
    {
        return false;
    }
    
    public function isEmpty():bool
    {
        return true;
    }
    
    public function getHasEmpty():bool
    {
        return true;
    }

    public function getView($indent)
    {
        $view='';
        for($i=0;$i<$indent;$i++)
            $view.='&nbsp;';

        $view.=str_repeat($this->state,$this->_length);

        

        if ($this->isFull())
            $view.=' '.implode(',',$this->getGroupNumbersKeys());
        elseif ($this->isUnknown())
        {
            $view.=' '.($this->getHasEmpty()?'hasEmpty':'');
        }

        $view.='<br>';


        return $view;
    }
    
    public function setEmptyCells(){}
}