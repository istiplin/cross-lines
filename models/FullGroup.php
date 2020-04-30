<?php
namespace models;

//класс для работы с группой разукрашенных соседних клеток
class FullGroup extends EmptyGroup
{
    protected $_state = Cell::FULL_STATE;
	
    public function isUnknown():bool
    {
        return false;
    }

    public function isFull():bool
    {
        return true;
    }

    public function isEmpty():bool
    {
        return false;
    }
    
    public function getHasEmpty():bool
    {
        return false;
    }

    public function setEmptyCells()
    {
		//ставим крестики вокру текущей группы, если её длина совпадает со значением всех её соответствующих чисел
		//0000011111000000=>0000211111200000		
        if ($this->getGroupNumbersMinLength()==$this->getGroupNumbersMaxLength())
		{
			if ($this->_groupNumbersMinLength==$this->_length)
			{
				$this->_cells->setEmpty($this->_start-1);
				$this->_cells->setEmpty($this->_end+1);
			}
			elseif($this->prevIsEmpty() AND $this->nextIsUnknown())
				$this->_cells->setEmpty($this->_start+$this->_groupNumbersMinLength);
			elseif($this->nextIsEmpty() AND $this->prevIsUnknown())
				$this->_cells->setEmpty($this->_end-$this->_groupNumbersMinLength);
		}
    }

    public function setFullCells()
    {
		//заполняем клетки слева направо
		//доходим до текущей группы, а затем закрашиваем
		
		$group = $this->getPrev();
		while($group!==null AND !$group->isEmpty() AND !($this->prevIsUnknown() AND $this->prevHasEmpty()))
			$group = $group->getPrev();
			
		if ($group===null)
			//|4|110000...->|4|111100...
			$noFullBeg = 0;
		elseif ($group->isEmpty())
			//|4|...2110000...->|4|...2111100...
			$noFullBeg = $group->getEnd()+1;
		elseif ($this->prevIsUnknown() AND $this->prevHasEmpty())
			//|...|4|...0110110000...->|...|4|...0110111100...
			$noFullBeg = $group->getStart()+1;

		$dist = $this->_end - $noFullBeg + 1;
		$minLength = $this->getGroupNumbersMinLength();
		if ($dist < $minLength)
		{
			$beg = $this->_end+1;
			$end = $noFullBeg + $minLength-1;
			$this->_cells->setFullStates($beg,$end);
		}
		
		//заполняем клетки справа налево
		//доходим до текущей группы, а затем закрашиваем
		$group = $this->getNext();
		while($group!==null AND !$group->isEmpty() AND !($this->nextIsUnknown() AND $this->nextHasEmpty()))
			$group = $group->getNext();
		
		if ($group===null)
			$noFullEnd = $this->_cells->getCount()-1;
		elseif ($group->isEmpty())
			$noFullEnd = $group->getStart()-1;
		elseif ($this->nextIsUnknown() AND $this->nextHasEmpty())
			$noFullEnd = $group->getEnd()-1;

		$dist = $noFullEnd - $this->_start + 1;
		$minLength = $this->getGroupNumbersMinLength();
		if ($dist < $minLength)
		{
			$end = $this->_start-1;
			$beg = $noFullEnd - $minLength+1;
			$this->_cells->setFullStates($beg,$end);
		}
		
    }

    public function setGroupNumbers()
    {
        $this->_groupNumbers = [];
		
        for ($i=0; $i<$this->_numbers->getCount(); $i++)
        {
            $minPos = $this->_numbers->getMinPos($i);
            $maxPos = $this->_numbers->getMaxPos($i);
            if ($minPos<=$this->_start AND $this->_end<=$maxPos)
                $this->_groupNumbers[$i] = $this->numbers[$i];
        }
		
        if (count($this->_groupNumbers)===0)
            throw new \Exception('error in '.__CLASS__.' '.$this->_cells->getData().' line '.$this->line->ind.' numberGroup '.$this->ind.' this->_groupNumbers has count=0.');
    }

    private function unsetGroupNumber($ind)
    {
        if (!array_key_exists($ind, $this->_groupNumbers))
            return;

        unset($this->_groupNumbers[$ind]);
        if (count($this->_groupNumbers)==0)
            throw new \Exception('error in '.__CLASS__.' line='.$this->line->ind.', numberGroup='.$this->ind.', currNum='.$ind.' this->_groupNumbers has count=0.');
    }


	
    public function deleteGroupNumbers()
    {
        //удаляем числа, не соответствующие данной группе, обходя клетки с левой и справой стороны до текущей группы
        $this->deleteGroupNumbersFromSideCell('left');
        $this->deleteGroupNumbersFromSideCell('right');

        foreach($this->_groupNumbers as $key=>$groupNumber)
        {
            //удаляем те числа длина которых меньше длины текущей группы
            if ($groupNumber->getLength()<$this->_length OR
                //или длина больше длины текущей группы, но между этой группой крестики
                $groupNumber->getLength()>$this->_length AND $this->nextIsEmpty() AND $this->prevIsEmpty())
                    $this->unsetGroupNumber($key);
        }

		//удаляем числа если длина группы получается больше
		if ($this->prevIsEmpty())
		{
			foreach($this->_groupNumbers as $key=>$groupNumber)
			{
				$ind = $this->_start+$groupNumber->getLength();
				if ($ind>=$this->_cells->getCount())
					continue;

				if ($this->cells[$ind]->isFull())
					$this->unsetGroupNumber($key);
			}
		}
		
		//удаляем числа если длина группы получается больше
		if ($this->nextIsEmpty())
		{
			foreach($this->_groupNumbers as $key=>$groupNumber)
			{
				$ind = $this->_end-$groupNumber->getLength();
				if ($ind<0)
					continue;
				
				if ($this->cells[$ind]->isFull())
					$this->unsetGroupNumber($key);
			}
		}
			
		
        if (count($this->_groupNumbers)==1)
        {
            $number = $this->numbers[key($this->_groupNumbers)];
            $number->setPos('max',$this->start+$number->getLength()-1);
            $number->setPos('min',$this->end-$number->getLength()+1);
        }

        return $this->_groupNumbers;
    }

    //удаляет возможные числа слева или справа
	
    public function deleteGroupNumbersOnBound(&$minInd,$boundType)
    {
        if ($boundType=='min')
        {
            $getGroupNumbersMinInd='getGroupNumbersMinInd';
            $reset = 'reset';
            $next = 'next';
            $getPrev = 'getPrev';
            $prevHasEmpty = 'prevHasEmpty';
            $condEqual = function($minInd,$ind){return $minInd>=$ind;};
            $condNoEqual = function($minInd,$ind){return $minInd>$ind;};
        }
        elseif($boundType=='max')
        {
            $getGroupNumbersMinInd='getGroupNumbersMaxInd';
            $reset = 'end';
            $next = 'prev';
            $getPrev = 'getNext';
            $prevHasEmpty = 'nextHasEmpty';
            $condEqual = function($minInd,$ind){return $minInd<=$ind;};
            $condNoEqual = function($minInd,$ind){return $minInd<$ind;};
        }
        
        if ($minInd===null)
        {
            $minInd = $this->$getGroupNumbersMinInd();
        }
        //если между текущей закрашенной группой и предыдущей закрашенной группой есть крестик
        else
        {
            //удаляем числа, которых нет у предыдущей группы, плюс первое число которое есть у предыдущей группы
            $groupNumbers = $this->_groupNumbers;
            $reset($groupNumbers);
            $ind = key($groupNumbers);
            
            $cond = $condNoEqual;
            if ($this->$prevHasEmpty() OR $this->$getPrev()->$prevHasEmpty())
                $cond = $condEqual;
            
            while (current($groupNumbers)!==false AND $cond($minInd,$ind))
            {
                $this->unsetGroupNumber($ind);
                $next($groupNumbers);
                $ind = key($groupNumbers);
            }
            $minInd = $ind;
        }
    }
	

    public function deleteGroupNumbersFromSideCell($side='left')
    {
        $ind = $this->_ind;
        
        $groups = $this->_groups;
        $numbers = $this->numbers;
        $cells = $this->cells;

        $currNum = 0;
        $begNumber = 0;

        $rightDir = 'right';
        $leftDir = 'left';
        
        $start = 'start';

        $setMaxPos = 'setMaxPos';
        $max = 'max';

        $step = 1;

        if ($side=='right')
        {
            $currNum = $numbers->count-1;
            $begNumber = $groups->count-1;

            $rightDir = 'left';
            $leftDir = 'right';
            
            $start = 'end';

            $setMaxPos = 'setMinPos';
            $max = 'min';

            $step = -1;
        }

        for ($i=$begNumber; $step*$i<$step*$ind; $i+=$step)
        {
            if (!$groups[$i]->isFull())
                continue;

            while (true) {

                $currLength = $numbers[$currNum]->getLength();
                //если текущее число меньше длины текущей группы
                if ($currLength<$groups[$i]->getLength()) {
                    //удаляем число в текущем объекте группы
                    $this->numbers[$currNum]->setPos($max,$groups[$i]->$start-2*$step);
                    $this->unsetGroupNumber($currNum);
                    $currNum+=$step;
                }
                //иначе
                else {
                    //определяем начальную координату текущей группы
                    $groupStart = $groups[$i]->$start;
                    
                    //определяем какая длина может поместиться, начиная с этой координаты
                    $fullLength = $cells->getFullLength($groupStart, $currLength, $rightDir);

                    $begPos = $groups[$i]->$start + $step * ($fullLength - 1);
                    $i = $cells[$begPos]->getGroup()->getInd();

                    if ($fullLength < $currLength) {
                        $fullLength = $cells->getFullLength($begPos, $currLength, $leftDir);
                        while ($fullLength < $currLength AND !$cells[$begPos]->isFull()) {
                            $begPos -= $step;
                            $fullLength = $cells->getFullLength($begPos, $currLength, $leftDir);
                        }

                        if ($fullLength < $currLength) {
                            $this->numbers[$currNum]->setPos($max,$groups[$i]->$start - 2 * $step);
                            $this->unsetGroupNumber($currNum);
                            $currNum += $step;
                        } else
                            break;
                    } else
                        break;
                }
            }

            if ($step*$i>=$step*$this->_ind)
                break;

            $this->unsetGroupNumber($currNum);
            $currNum+=$step;
        }
    }
	
	public function deleteUnknownGroupNumbers()
	{
		$groupNumber = reset($this->_groupNumbers);
		$currBegInd = $groupNumber->getInd();
		$currLength = $groupNumber->getLength();
		$group = $this->getNext();
		while ($group!==null)
		{
			if (!$group->isUnknown())
			{
				$group = $group->getNext();
				continue;
			}
			$groupNumbers = $group->getGroupNumbers();
			if (count($groupNumbers))
			{
				$groupNumber = reset($groupNumbers);
				$begInd = $groupNumber->getInd();
				for ($i=$begInd; $i<$currBegInd; $i++)
					$group->unsetGroupNumber($i);
					
				if ($currLength==$this->_length)
					$group->unsetGroupNumber($currBegInd);
			}
			$group = $group->getNext();
		}
		
		
		$groupNumber = end($this->_groupNumbers);
		$currBegInd = $groupNumber->getInd();
		$currLength = $groupNumber->getLength();
		$group = $this->getPrev();
		while ($group!==null)
		{
			if (!$group->isUnknown())
			{
				$group = $group->getPrev();
				continue;
			}
			
			$groupNumbers = $group->getGroupNumbers();
			if (count($groupNumbers))
			{
				$groupNumber = end($groupNumbers);
				$endInd = $groupNumber->getInd();
				for ($i=$endInd; $i>$currBegInd; $i--)
					$group->unsetGroupNumber($i);
					
				if ($currLength==$this->_length)
					$group->unsetGroupNumber($currBegInd);
			}
			$group = $group->getPrev();
		}
		
	}
}