<?php
namespace models;

use \sys\BaseObject;
use \sys\TArrayAccess;

//класс для работы с клетками, по которым строится рисунок
class Cells extends BaseObject implements \ArrayAccess
{
    use TArrayAccess;
    
    private $_line;
    private $_list;
    private $_count=0;
    private $_unknownCount;

    public function __construct($line,$data)
    {
        $this->_line = $line;
        $this->setList($data);
    }

    private function getCell($state,$ind)
    {
        if ($this->_line->field!==null)
            $cell = $this->_line->field->getCell($state,$ind,$this->_line);
        else
            $cell = new Cell($state);
        
        return $cell;
    }
    
    private function setList($data)
    {
        $this->_unknownCount=0;
        $elem = null;
        for($i=0; $i<strlen($data); $i++)
        {	
            $state = $data[$i];
            $elem = $this->getCell($state,$i);
            $this->_list[$i] = $elem;
            $this->_count++;
            if ($elem->isUnknown())
                $this->_unknownCount++;
        }
    }
    
    public function changeAttrs()
    {
        $count = count($this->_list);
        $prevCell = null;
        for($i=0; $i<$count; $i++)
        {
            $cell = $this->_list[$i];
            $cell->cells = $this;
            $cell->ind = $i;
            $cell->prev = $prevCell;
            $prevCell = $cell;
        }
        $prevCell->next=null;
    }

    //возвращает итоговую длину возможной закрашенной группы,
    //начиная с позиции $fullBegPos, пытаясь пройти  расстояние длиной $fullLength
    //в направлении $direction
    public function getFullLength($fullBegPos, $fullLength, $direction): int
    {
        if ($fullLength==0)
            throw new \Exception('$fullLength is 0');

        if ($this[$fullBegPos]->isEmpty())
            throw new \Exception('$fullBegPos is empty');

        $resFullLength = 0;
        $currPos = $fullBegPos;

        $groupStart = 'groupStart';
        $groupEnd = 'groupEnd';
        $nextGroupIsEmpty = 'nextGroupIsEmpty';
        $next = 'next';
        $step = 1;
        if ($direction == 'left')
        {
            $groupStart = 'groupEnd';
            $groupEnd = 'groupStart';
            $nextGroupIsEmpty = 'prevGroupIsEmpty';
            $next = 'prev';
            $step = -1;
        }

        if ($this[$currPos]->isFull())
        {
            if ($currPos !== $this[$currPos]->$groupStart)
                throw new \Exception("currPos=$currPos is not $groupStart = {$this[$currPos]->$groupStart} (fullBegPos=$fullBegPos, fullLength=$fullLength, $direction)");
            if ($fullLength < $this[$currPos]->groupLength)
                return 0;
            if ($fullLength == $this[$currPos]->groupLength)
                return $fullLength;
        }

        while(true) {

            if ($this[$currPos]->isFull()) {
                $resFullLength += ($step*($this[$currPos]->$groupEnd - $currPos) + 1);
            }
            elseif ($this[$currPos]->isUnknown()) {
                $resFullLength += ($step*($this[$currPos]->$groupEnd - $currPos) + 1);
                if ($resFullLength > $fullLength)
                    return $fullLength;
                if ($this[$currPos]->$nextGroupIsEmpty)
                    return $resFullLength;
                if ($resFullLength == $fullLength)
                    return $resFullLength - 1;

                $nextLength = $this[$currPos]->group->$next->length;
                if ($fullLength < $resFullLength + $nextLength)
                    return $resFullLength - 1;

            }
            if ($this[$currPos]->$nextGroupIsEmpty)
                break;
            $currPos = $this[$currPos]->$groupEnd + $step;
        }

        return $resFullLength;
    }

    //определяет позицию клетки, с которой начинается и может поместиться закрашенная группа длинной $fullLength
    //при этом, чтобы длина сохранялась такой
    //начиная поиск с позиции $begPos в направлении $direction
    public function getFullBegPos($fullBegPos, $fullLength, $direction): ?int
    {
        $next = 1;
        $groupStart = 'groupStart';
        $groupEnd = 'groupEnd';
        $nextGroupIsEmpty = 'nextGroupIsEmpty';
        $prevIsFull = 'prevIsFull';
        if ($direction=='left')
        {
            $next = -1;
            $groupStart = 'groupEnd';
            $groupEnd = 'groupStart';
            $nextGroupIsEmpty = 'prevGroupIsEmpty';
            $prevIsFull = 'nextIsFull';
        }
        
        if (-1<$fullBegPos AND $fullBegPos<$this->_count)
        {
            if ($this[$fullBegPos]->isUnknown() AND $this[$fullBegPos]->$prevIsFull())
            {
                $fullBegPos+=$next;
            }
        }
        
        $fullBegPos = $currPos = $fullBegPos;
        
        while(-1<$currPos AND $currPos<$this->_count)
        {
            //если текущая клетка - крестик
            if ($this[$currPos]->isEmpty())
                //смещаем указатели в следующую группу
                $fullBegPos = $currPos = $this[$currPos]->$groupEnd + $next;
            //иначе если текущая клетка не заполнена
            elseif ($this[$currPos]->isUnknown()) {
                //определяем конечную позицию незаполненной группы
                $endUnknownPos = $this[$currPos]->$groupEnd;
                //определяем расстояние от текущей пока что результирующей позиции до конечной позиции текущей группы
                $dist = $this[$fullBegPos]->getDist($endUnknownPos);
                //если расстояние больше текущего числа или
                if ($dist > $fullLength OR
                    //расстояние равно текущему числу и следующая группа крестик или конец
                    $dist == $fullLength AND $this[$currPos]->$nextGroupIsEmpty)
                    //считаем последеняя пока что результирующая позиция, окончательно ей являестя
                    return $fullBegPos;
                else
                    //иначе смещаем указатель к следующей группе
                    $currPos = $this[$currPos]->$groupEnd + $next;
            }
            //иначе если текущая клетка закрашена
            elseif ($this[$currPos]->isFull()) {
                //если длина текущей группы больше текущего числа
                if ($this[$currPos]->groupLength > $fullLength)
                    //смещаем указатели на 2 позиции вправо, мысленно полагая, что следующая клетка крестик, а потом идет разукрашенная
                    $fullBegPos = $currPos = $this[$currPos]->$groupEnd + $next*2;
                //если длина текущей группы равно текущему числу
                elseif ($this[$currPos]->groupLength == $fullLength)
                    //считаем последеняя пока что результирующая позиция, окончательно ей являестя
                    return $this[$currPos]->$groupStart;
                //если длина текущей группы меньше текущего числа
                elseif ($this[$currPos]->groupLength < $fullLength) {
                    //определяем конечную позицию текущей группы
                    $endFullPos = $this[$currPos]->$groupEnd;
                    //определяем расстояние от текущей пока что результирующей позиции до конечной позиции текущей группы
                    $dist = $this[$fullBegPos]->getDist($endFullPos);

                    //если расстояние меньше текущего числа
                    if ($dist < $fullLength)
                        //иначе смещаем указатель к следующей группе
                        $currPos = $this[$currPos]->$groupEnd + $next;
                    //иначе если расстояние равно текущему числу
                    elseif ($dist == $fullLength)
                        //считаем, что последняя, пока что, результирующая позиция, окончательно ей являестя
                        return $fullBegPos;
                    //иначе если расстояние больше текущего числа
                    elseif ($dist > $fullLength)
                    {
                        //если результирующая позиция находится в незаполненной клетке
                        if ($this[$fullBegPos]->isUnknown())
                            //считаем, что она окончательно является результирующей
                            $fullBegPos = $endFullPos - $next*($fullLength - 1);
                        //иначе если  результирующая позиция находится в закрашенной клетке
                        elseif ($this[$fullBegPos]->isFull())
                            //смещаем её на 2 позиции вправо, мысленно полагая, что следующая клетка крестик, а потом идет разукрашенная
                            $fullBegPos = $this[$fullBegPos]->$groupEnd + $next*2;
                    }
                }
            }
        }
        return null;
    }

    public function getNumbers(): Numbers
    {
        return $this->_line->numbers;
    }
    
    public function getUnknownCount(): int
    {
        return $this->_unknownCount;
    }
    
    public function decrUnknownCount($pos)
    {
        $this->_unknownCount--;
        if ($this->_unknownCount<0)
            throw new \Exception(' error line:'.$this->line->ind.' pos:'.$pos.'. this->unknownCount is bellow zero');
    }
    
    public function getList(): array
    {
        return $this->_list;
    }
    
    public function getLine()
    {
        return $this->_line;
    }
    
    public function getCount(): int
    {
        return $this->_count;
    }
    
    public function setFullStates($start,$end)
    {
        for ($i=$start; $i<=$end; $i++)
            $this->_list[$i]->setFull();
    }
    
    public function setEmptyStates($start,$end)
    {
        for ($i=$start; $i<=$end; $i++)
            $this->_list[$i]->setEmpty();
    }

    public function getView()
    {
        $view='';
        for ($i = 0; $i<$this->_count; $i++)
            $view.=$this->list[$i]->state;
        return $view;
    }
    
    public function view()
    {
        $this->numbers->view();
        echo ' ';
        for($i=0; $i<$this->count; $i++)
            echo $this->list[$i]->state;
        echo '<br>';
    }
}