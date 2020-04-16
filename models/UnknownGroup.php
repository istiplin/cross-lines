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
        
        $length = $this->length;

        $prevMax = 0;
        $nextMax = 0;
        if ($this->prevIsFull())
        {
            $length += $this->prev->length;
            $prevMax = $this->prev->getGroupNumbersMaxLength();
        }
        else
             //считаем, что текущая группа может быть полностью закрашена, т.е. крестика тут может не быть
            return $this->_hasEmpty = false;
        
        if ($this->nextIsFull())
        {
            $length += $this->next->length;
            $nextMax = $this->next->getGroupNumbersMaxLength();
        }
        else
            //считаем, что текущая группа может быть полностью закрашена, т.е. крестика тут может не быть
            return $this->_hasEmpty = false;
        
        $min = min($prevMax,$nextMax);
        
        //если минимальная из максимальных длин закрашенной группы не превышает полученную длину из текущей группы и его соседних
        if ($min<$length)
            //считаем, что текущая группа не может быть полностью закрашена, т.е. крестик в текущей группе точно есть
            return $this->_hasEmpty = true;
        //иначе считаем, что текущая группа может быть полностью закрашена, т.е. крестика тут может не быть
        return $this->_hasEmpty = false;
    }
    
    private function getFullGroup($type): ?FullGroup
    {
        $prevFullGroup = null;
        $prev = $this->$type;
        while ($prev)
        {
            if ($prev->isFull())
            {
                $prevFullGroup = $prev;
                break;
            }
            $prev = $prev->$type;
        }
        
        return $prevFullGroup;
    }
    
    public function setEmptyCells()
    {
        if ($this->prevIsEmpty() AND $this->nextIsEmpty())
        {
            $minLength = $this->cells->count;
            $begKey = 0;
            $endKey = $this->numbers->count-1;
            
            if ($prevFullGroup = $this->getFullGroup('prev'))
            {
                $groupNumbers = $prevFullGroup->groupNumbers;
                reset($groupNumbers);
                $begKey = key($groupNumbers)+1;
            }
            
            if ($nextFullGroup = $this->getFullGroup('next'))
            {
                $groupNumbers = $nextFullGroup->groupNumbers;
                end($groupNumbers);
                $endKey = key($groupNumbers)-1;
            }
            for($key=$begKey; $key<=$endKey; $key++)
                $minLength = min($minLength,$this->numbers[$key]->length);
            
            if ($this->_length<$minLength)
                for ($pos=$this->_start; $pos<=$this->_end; $pos++)
                    $this->cells[$pos]->setEmpty();
        }
    }

	public function resolveByClone()
	{
		if ($this->prevIsFull() AND $this->nextIsFull())
		{
			$cellList = $this->_cells->getList();
			if ($this->_length==1 AND $cellList[$this->_start]->isUnknown())
			{
				//echo $this->_line->isHorizontal.','.$this->_line->ind.','.$this->_start.' ';
				
				$line = clone $this->getLine();
				$line->getCells()->setEmptyStates($this->_start);
				
				if (!$line->resolveTest())
					$this->_cells->setFullStates($this->_start);
			}	
		}
	}

}