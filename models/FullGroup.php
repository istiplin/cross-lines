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
    
    public function setGroupNumbers()
    {
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
        if (count($this->_groupNumbers)===0)
            throw new \Exception('error in '.__CLASS__.' line '.$this->line->ind.' number '.$this->ind.' this->_groupNumbers has count=0.');
    }

    public function getGroupNumbers()
    {
        if ($this->_groupNumbers!==null)
            return $this->_groupNumbers;
        return $this->_groupNumbers = $this->numbers->list;
    }

    public function getGroupNumbersKeys()
    {
        return array_keys($this->_groupNumbers);
    }

    private function unsetGroupNumber($ind)
    {
        if (!array_key_exists($ind, $this->_groupNumbers))
            return;   
        unset($this->_groupNumbers[$ind]);
        if (count($this->_groupNumbers)==0)
            throw new \Exception('error in '.__CLASS__.' line '.$this->line->ind.' number '.$this->ind.' this->_groupNumbers has count=0.');
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
            $number->setMaxPos($this->start+$number->length-1);
            $number->setMinPos($this->end-$number->length+1);
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

    /*
    public function deleteGroupNumbersFromSideCell2($side='left')
    {
        $groups = $this->_groups;
        $numbers = $this->numbers;
        $currPos = 0;
        $currNum = 0;
        for ($i=0; $i<=$this->_ind; $i++)
        {
            if ($groups[$i]->isFull())
            {
                while ($groups[$i]->length>$this->numbers[$currNum]->length)
                {
                    $currPos += ($numbers[$currNum]->length-1)+1;
                    $currPos = $this->cells->getNextFullPosToRight($begPos, $fullLength);
                    $this->unsetGroupNumber($currNum);
                    $currNum++;
                }
            }
        }
    }
    */

    //удаляет возможные числа, большинство которых удаляется для групп, расположенных по середине
    private function deleteGroupNumbersFromSideCell($side='left')
    {
        if ($side=='left')
        {
            $currPN = 0;
            
            $groupsBeg = 0;
            $groupsCond = function($i,$end){return $i<=$end;};
            
            $next = 1;
            $nextIsEmpty = 'nextIsEmpty';

            $setMaxPos = 'setMaxPos';

            $start = 'start';
        }
        elseif ($side=='right')
        {
            $currPN = $this->numbers->count-1;
            
            $groupsBeg = $this->_groups->count-1;
            $groupsCond = function($i,$end){return $i>=$end;};
            
            $next = -1;
            $nextIsEmpty = 'prevIsEmpty';

            $setMaxPos = 'setMinPos';

            $start = 'end';
        }
        
        $list = $this->_groups;
        $numbers = $this->numbers;
        $numberLength = 0;
        //обходим группы удаляя те номера, которые точно не принадлежат текущей группе
        for($i=$groupsBeg; $groupsCond($i,$this->_ind); $i+=$next)
        {
            if ($list[$i]->isEmpty())
            {
                //обнуляем остаток от числа
                $numberLength = 0;
            }
            elseif ($list[$i]->isFull())
            {
                //определяем количество клеток в группе
                $groupsLength = $list[$i]->length;
                
                //определяем следующее число, если остаток от предыдущего числа не остался
                if ($numberLength==0)
                    $numberLength = $numbers[$currPN]->length;

                //пока количество клеток в группе больше текущего числа
                while ($numberLength<$groupsLength)
                {
                    //считаем, что текущее число лежит левее от текущей группы
                    $this->numbers[$currPN]->$setMaxPos($list[$i]->$start-2*$next);
                    $this->unsetGroupNumber($currPN);
                    $currPN+=$next;
                    
                    //получаем следующее число для проверки
                    $numberLength = $numbers[$currPN]->length;
                }

                if ($i==$this->_ind)
                    break;
                
                //если текущее число равно количеству клеток в группе
                if ($numberLength==$groupsLength)
                {
                    //считаем, что текущее число соответствует текущей группе
                    $this->unsetGroupNumber($currPN);
                    $currPN+=$next;

                    $numberLength = 0;
                    
                    //переходим к следующей группе
                    continue;
                }
                //если текущее число больше количеству клеток в группе
                if ($numberLength>$groupsLength)
                    //получаем остаток от числа вычитая количество клеток в группе
                    $numberLength -= $groupsLength;

            }
            elseif ($list[$i]->isUnknown())
            {
                //если остаток от числа остался
                if ($numberLength>0)
                {
                    //определяем количество незаполненных клеток в группе
                    $groupsLength = $list[$i]->length;
                    
                    //получаем остаток от числа вычитая количество клеток в группе
                    $numberLength -= $groupsLength;
                    
                    //если от остатка ничего неосталось
                    if ($numberLength<=0)
                    {
                        //считаем, что следующее число соответствует следующей группе
                        $this->unsetGroupNumber($currPN);
                        $currPN+=$next;
                        
                        $numberLength = 0;
                    }
                        
                }
            }
        }
    }
}