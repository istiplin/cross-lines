<?php
namespace models;

use \sys\BaseObject;

//класс для работы с группой соседних клеток, помеченные крестиками
class EmptyGroup extends BaseObject
{
    protected $_state = Cell::EMPTY_STATE;
	
	protected $_field;
    protected $_groups;
	protected $_cells;
	protected $_line;
	protected $_numbers;

    protected $_start;
    protected $_length;
    protected $_end;
    protected $_ind;

    protected $_prev;
    protected $_next;
	
	protected $_groupNumbers;
    protected $_groupNumbersMinLength;
    protected $_groupNumbersMaxLength;

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
		$this->setGroups($groups);
	
		$this->setPrev($prev);
		
        $this->_start = $start;
        $this->_ind = $ind;
    }

	public function setGroups(Groups $value)
	{
		$this->_groups = $value;
		$this->_cells = $value->getCells();
		$this->_line = $value->getLine();
		$this->_field = $this->_line->getField();
		$this->_numbers = $this->_line->getNumbers();
	}
	
    public function getStart(): int
    {
        return $this->_start;
    }

    public function setEnd(int $value)
    {
        if ($this->_end!==null)
            throw new \Exception('Error! '.__METHOD__.' $this->_end is not null');
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
        return $this->_cells;
    }
    
    public function getNumbers(): Numbers
    {
        return $this->_numbers;
    }

    public function getLine(): Line
    {
        return $this->_groups->getCells()->getLine();
    }

    public function setPrev($prev)
    {
	
		//устанавливаем ссылку на предыдую группу
        $this->_prev = $prev;
		
		//в предыдущей группе устанавливаем ссылку на текущую группу
		if ($prev!==null)
		{
			if ($prev->getState()==$this->getState())
				throw new \Exception('Prev group state='.$this->getState().' is same current group state');
				
			$prev->setNext($this);
		}
    }
    
    public function getPrev(): ?self
    {
        return $this->_prev;
    }

    public function setNext(self $next)
    {		
        if ($next->getState()==$this->_state)
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
	
	public function getPrevStart()
	{
		return $this->_prev->getStart();
	}
	
	public function getNextEnd()
	{
		return $this->_next->getEnd();
	}
	
	public function getPrevLength(): int
	{
		if ($this->_prev===null OR $this->_prev->isEmpty())
			return 0;
		return $this->_prev->getLength();
	}
	
	public function getNextLength(): int
	{
		if ($this->_next===null OR $this->_next->isEmpty())
			return 0;
		return $this->_next->getLength();
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

	
	public function getCellsView()
	{
		$cellsData = $this->_cells->getData();
		$cellsView = '';
		for ($i=0; $i<strlen($cellsData); $i++)
		{
			if ($this->_start<=$i AND $i<=$this->_end)
				$cellsView.='<b>'.$cellsData[$i].'</b>';
			else
				$cellsView.=$cellsData[$i];
		}
		return $cellsView;
	}
	
    public function getView()
    {
		$view='';
        if ($this->isFull())
		{
            $view.=' '.$this->_numbers->getLengthView($this->getGroupNumbersKeys()).' ';
			//else
			//	$view.=' '.$this->_numbers->getLengthView().' ';
				
			//if ($this->isUnknown())
			//	$view.=' '.($this->getHasEmpty()?'hasEmpty':'');

			$view.=$this->getCellsView();
			
			$view.=' group-numbers<br>';
		}
        return $view;
    }
	
    public function setEmptyCells(){}
	public function setFullCells(){}
    public function setGroupNumbers(){}
	

	
    public function getGroupNumbers(): array
    {
        return $this->_groupNumbers;
    }
    
    public function getGroupNumbersKeys()
    {
        return array_keys($this->_groupNumbers);
    }
	
	public function getBegGroupNumber()
	{
        return reset($this->_groupNumbers);
	}
	
	public function getEndGroupNumber()
	{
        return end($this->_groupNumbers);
	}

	public function getGroupNumbersCount():int
	{
		return count($this->_groupNumbers);
	}
	
	public function getGroupNumberLength($ind=null):int
	{
		if ($ind!==null)
			return $this->_groupNumbers[$ind]->getLength();
			
		if (count($this->_groupNumbers)==1)
			return reset($this->_groupNumbers)->getLength();

		throw new \Exception(__METHOD__.' $ind is null');
	}
	
    public function getGroupNumbersMinInd():int
    {
        return reset($this->_groupNumbers)->getInd();
    }
	
    public function getGroupNumbersMaxInd():int
    {
        return end($this->_groupNumbers)->getInd();
    }
    
    public function getGroupNumbersMinLength():int
    {
        if ($this->_groupNumbersMinLength!==null)
            return $this->_groupNumbersMinLength;
        
        $groupNumber = reset($this->_groupNumbers);
        $min = $groupNumber->getLength();
        foreach ($this->_groupNumbers as $groupNumber)
            $min = min($min,$groupNumber->getLength());
        
        return $this->_groupNumbersMinLength = $min;
    }
    
    public function getGroupNumbersMaxLength():int
    {
        if ($this->_groupNumbersMaxLength!==null)
            return $this->_groupNumbersMaxLength;
        
        $groupNumber = reset($this->_groupNumbers);
        $max = $groupNumber->getLength();
        foreach ($this->_groupNumbers as $groupNumber)
            $max = max($max,$groupNumber->getLength());
        
        return $this->_groupNumbersMaxLength = $max;
    }
}