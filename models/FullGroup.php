<?php
namespace models;

//класс для работы с группой разукрашенных соседних клеток
class FullGroup extends EmptyGroup
{
    protected $_state = Cell::FULL_STATE;
    private $_groupNumbers;
    
    private $_groupNumbersMinInd;
    private $_groupNumbersMaxInd;
    
    private $_groupNumbersMinLength;
    private $_groupNumbersMaxLength;

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
    
    public function setGroupNumbers($value=null)
    {
        if ($value!==null)
            $this->_groupNumbers = $value;

        if ($this->_groupNumbers!==null)
            return;


        $this->_groupNumbers = [];
        for ($i=0; $i<$this->numbers->count; $i++)
        {
            $minPos = $this->numbers->getMinPos($i);
            $maxPos = $this->numbers->getMaxPos($i);
            if ($minPos<=$this->_start AND $this->_end<=$maxPos)
                $this->_groupNumbers[$i] = $this->numbers[$i];
        }

        //$this->_groupNumbers = $this->numbers->list;
        if (count($this->_groupNumbers)===0)
            throw new \Exception('error in '.__CLASS__.' line '.$this->line->ind.' number '.$this->ind.' this->_groupNumbers has count=0.');
    }

    public function getGroupNumbers()
    {
        return $this->_groupNumbers;
    }
    
    public function getGroupNumbersKeys()
    {
        return array_keys($this->_groupNumbers);
    }

    private function unsetGroupNumber($ind)
    {
        if (!array_key_exists($ind, $this->_groupNumbers))
            return;

        //if (count($this->_groupNumbers)>1)
            unset($this->_groupNumbers[$ind]);
        if (count($this->_groupNumbers)==0)
            throw new \Exception('error in '.__CLASS__.' line='.$this->line->ind.', numberGroup='.$this->ind.', currNum='.$ind.' this->_groupNumbers has count=0.');
    }

    public function getGroupNumbersMinInd():int
    {
        if ($this->_groupNumbersMinInd!==null)
            return $this->_groupNumbersMinInd;

        $groupNumbers = $this->_groupNumbers;
        reset($groupNumbers);
        return $this->_groupNumbersMinInd = key($groupNumbers);
    }
    
    public function getGroupNumbersMaxInd():int
    {
        if ($this->_groupNumbersMaxInd!==null)
            return $this->_groupNumbersMaxInd;

        $groupNumbers = $this->_groupNumbers;
        end($groupNumbers);
        return $this->_groupNumbersMaxInd = key($groupNumbers);
    }
    
    public function getGroupNumbersMinLength():int
    {
        if ($this->_groupNumbersMinLength!==null)
            return $this->_groupNumbersMinLength;
        
        $groupNumbers = $this->_groupNumbers;
        $groupNumber = reset($groupNumbers);
        $min = $groupNumber->length;
        foreach ($groupNumbers as $groupNumber)
            $min = min($min,$groupNumber->length);
        
        return $this->_groupNumbersMinLength = $min;
    }
    
    public function getGroupNumbersMaxLength():int
    {
        if ($this->_groupNumbersMaxLength!==null)
            return $this->_groupNumbersMaxLength;
        
        $groupNumbers = $this->_groupNumbers;
        $groupNumber = reset($groupNumbers);
        $max = $groupNumber->length;
        foreach ($groupNumbers as $groupNumber)
            $max = max($max,$groupNumber->length);
        
        return $this->_groupNumbersMaxLength = $max;
    }

    public function setEmptyCells()
    {
        if ($this->getGroupNumbersMinLength()!==$this->getGroupNumbersMaxLength() OR
            $this->getGroupNumbersMinLength()!==$this->_length)
            return;

        $pos = $this->_start-1;
        if ($pos>=0)
            $this->cells[$pos]->setEmpty();

        $pos = $this->_start+$this->_length;
        if ($pos<$this->cells->count)
            $this->cells[$pos]->setEmpty();
    }

    public function setFullCells()
    {
        if ($this->prevIsEmpty() OR $this->prev->prev!==null AND $this->prev->prevIsEmpty())
        {
            $minLength = $this->getGroupNumbersMinLength();
            if ($this->prevIsEmpty())
                $minLength-=$this->length;
            elseif ($this->prev->prev!==null AND $this->prev->prevIsEmpty())
                $minLength-=($this->length+$this->prev->length);

            $beg = $this->end+1;
            for ($i=0; $i<$minLength; $i++)
                $this->cells[$beg+$i]->setFull();
        }

        if ($this->nextIsEmpty() OR $this->next->next!==null AND $this->next->nextIsEmpty())
        {
            $minLength = $this->getGroupNumbersMinLength();
            if ($this->nextIsEmpty())
                $minLength-=$this->length;
            elseif ($this->next->next!==null AND $this->next->nextIsEmpty())
                $minLength-=($this->length+$this->next->length);

            $beg = $this->start-1;
            for ($i=0; $i<$minLength; $i++)
                $this->cells[$beg-$i]->setFull();
        }

        if ($this->prevIsUnknown() AND $this->prevHasEmpty())
        {
            $minLength = $this->getGroupNumbersMinLength();
            $minLength-=($this->prev->length-1+$this->length);
            $beg = $this->end+1;
            for ($i=0; $i<$minLength; $i++)
                $this->cells[$beg+$i]->setFull();
        }

        if ($this->nextIsUnknown() AND $this->nextHasEmpty())
        {
            $minLength = $this->getGroupNumbersMinLength();
            $minLength-=($this->next->length-1+$this->length);
            $beg = $this->start-1;
            for ($i=0; $i<$minLength; $i++)
                $this->cells[$beg-$i]->setFull();
        }
    }

    public function deleteGroupNumbers()
    {
        //удаляем числа, не соответствующие данной группе, обходя клетки с левой и справой стороны до текущей группы
        $this->deleteGroupNumbersFromSideCell('left');
        $this->deleteGroupNumbersFromSideCell('right');

        foreach($this->_groupNumbers as $key=>$groupNumber)
        {
            //удаляем те числа длина которых меньше длины текущей группы
            if ($groupNumber->length<$this->length OR
                //или длина больше длины текущей группы, но между этой группой крестики
                $groupNumber->length>$this->length AND $this->nextIsEmpty() AND $this->prevIsEmpty())
                    $this->unsetGroupNumber($key);
        }

        if (count($this->_groupNumbers)==1)
        {
            $number = $this->numbers[key($this->_groupNumbers)];
            $number->setPos('max',$this->start+$number->length-1);
            $number->setPos('min',$this->end-$number->length+1);
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

                $currLength = $numbers[$currNum]->length;
                //если текущее число меньше длины текущей группы
                if ($currLength<$groups[$i]->length) {
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
                    $i = $cells[$begPos]->group->ind;

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
}