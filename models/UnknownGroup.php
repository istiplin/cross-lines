<?php
namespace models;

//класс для работы с группой непомеченных соседних клеток
class UnknownGroup extends EmptyGroup
{
    protected $_state = Cell::UNKNOWN_STATE;
	
    private $_hasEmpty;

    public function isUnknown():bool
    {
        return true;
    }

    public function isFull():bool
    {
        return false;
    }

    public function isEmpty():bool
    {
        return false;
    }

    public function getHasEmpty():bool
    {
        //return false;
        if ($this->_hasEmpty!==null)
            return $this->_hasEmpty;
        
		if ($this->prevIsEmpty() OR $this->nextIsEmpty())
			return $this->_hasEmpty = false;

		
		$prevMax = $this->getPrev()->getGroupNumbersMaxLength();
		$nextMax = $this->getNext()->getGroupNumbersMaxLength();
		
        $min = min($prevMax,$nextMax);
        $length = $this->length + $this->getPrev()->getLength() + $this->getNext()->getLength();
		
        //если минимальная из максимальных длин закрашенной группы не превышает полученную длину из текущей группы и его соседних
        if ($min<$length)
            //считаем, что текущая группа не может быть полностью закрашена, т.е. крестик в текущей группе точно есть
            return $this->_hasEmpty = true;
        //иначе считаем, что текущая группа может быть полностью закрашена, т.е. крестика тут может не быть
        return $this->_hasEmpty = false;
    }
	
    private function getFullGroup($direction): ?FullGroup
    {
        $prevFullGroup = null;
        $prev = $this->$direction;
        while ($prev)
        {
            if ($prev->isFull())
            {
                $prevFullGroup = $prev;
                break;
            }
            $prev = $prev->$direction;
        }
        
        return $prevFullGroup;
    }
    
    public function setEmptyCells()
    {
		$prevFullGroup = $this->getFullGroup('prev');
		$nextFullGroup = $this->getFullGroup('next');
		
        if ($this->prevIsEmpty() AND $this->nextIsEmpty())
        {
            $begKey = 0;
            if ($prevFullGroup)
				$begKey = $prevFullGroup->getGroupNumbersMinInd()+1;
				
			$endKey = $this->_numbers->getCount()-1;
            if ($nextFullGroup)
				$endKey = $nextFullGroup->getGroupNumbersMaxInd()-1;
				
			$minLength = $this->_numbers->getMinLength($begKey, $endKey);   
            if ($this->_length<$minLength)
				$this->_cells->setEmptyStates($this->_start,$this->_end);
	
        }
		
		/*
		if ($prevFullGroup AND $nextFullGroup)
		{
			$minInd = $prevFullGroup->getGroupNumbersMinInd();
			$maxInd = $nextFullGroup->getGroupNumbersMaxInd();
			if (($maxInd - $minInd)==1)
			{
				$start = $prevFullGroup->getStart()+$prevFullGroup->getGroupNumberLength($minInd);
				$end = $nextFullGroup->getEnd()-$nextFullGroup->getGroupNumberLength($maxInd);
				$this->_cells->setEmptyStates($start,$end);
			}
		}
		*/
		
		//|5|...20000111...->|5|...22200111...
		if ($this->prevIsEmpty() AND $this->nextIsFull())
		{
            $begKey = 0;
            if ($prevFullGroup)
				$begKey = $prevFullGroup->getGroupNumbersMinInd()+1;
				
			$endKey = $nextFullGroup->getGroupNumbersMaxInd();
			
			$minLength = $this->_numbers->getMinLength($begKey, $endKey);
			$maxLength = $this->_numbers->getMaxLength($begKey, $endKey);
			$sumLength = $this->_length+$this->getNextLength();
			if ($minLength>=$this->_length AND $maxLength<$sumLength)
			{
				$end = $this->getNextEnd()-$maxLength;
				$this->_cells->setEmptyStates($this->_start,$end);
			}
		}
		
		//|5|...11100002...->|5|...11100222...
		if ($this->prevIsFull() AND $this->nextIsEmpty())
		{

			$begKey = $prevFullGroup->getGroupNumbersMinInd();
			
			$endKey = $this->_numbers->getCount()-1;
			if ($nextFullGroup)
				$endKey = $nextFullGroup->getGroupNumbersMaxInd()-1;
			
			$minLength = $this->_numbers->getMinLength($begKey, $endKey);
			$maxLength = $this->_numbers->getMaxLength($begKey, $endKey);
			$sumLength = $this->getPrevLength()+$this->_length;
			if ($minLength>=$this->_length AND $maxLength<$sumLength)
			{
				$start = $this->getPrevStart()+$maxLength;
				$this->_cells->setEmptyStates($start,$this->_end);
			}
		}
		
    }
	
    public function setFullCells()
    {

    }

    public function solveByClone()
    {
        if ($this->prevIsFull() AND $this->nextIsFull())
        {
            $cellList = $this->_cells->getList();
            if ($this->_length==1 AND $cellList[$this->_start]->isUnknown())
            {
                //echo $this->_line->isHorizontal.','.$this->_line->ind.','.$this->_start.'<br>';

                $line = clone $this->_line;
                $line->getCells()->setEmptyStates($this->_start);

                if (!$line->trySolveTest())
                    $this->_cells->setFullStates($this->_start);
            }	
        }
    }
	
}