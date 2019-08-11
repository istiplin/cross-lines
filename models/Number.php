<?php
namespace models;

use \sys\BaseObject;

//класс для работы с числами, характеризующие длину разукрашенного сегмента в поле, состоящего из клеток
class Number extends BaseObject
{
    private $_numbers;
        
    private $_minPos;	//минимально возможная координата положения начала сегмента
    private $_maxPos;	//максимально возможная координата положения конца сегмента

    private $_length;		//длина закрашенной группы на рисунке
    private $_ind;			//номер индекса
    private $_prev;
    private $_next;

    private $_isOneGroup = false;   //число соответствует одной разукрашенной групее

    public function __construct($numbers,$length,$ind,$prev=null)
    {
        $this->_numbers = $numbers;
        $this->_length = $length;
        $this->_ind = $ind;

        if ($prev!==null)
        {
            //устанавливаем ссылку на предыдущий отрезок
            $this->prev = $prev;

            //в предыдущем отрезке устанавливаем ссылку на текущий отрезок
            $this->prev->next = $this;
        }
    }
	
    public function getLine(): Line
    {
        return $this->_numbers->line;
    }

    public function getCells(): Cells
    {
        return $this->_numbers->line->cells;
    }

    public function getNumbers(): Numbers
    {
        return $this->_numbers;
    }
        
    public function getLength():int
    {
        return $this->_length;
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

    public function issetPos($type):bool
    {
        $posName = '_'.$type.'Pos';
        if ($this->$posName===null)
            return false;
        else
            return true;
    }


    public function getPos($type):int
    {
        $minPos = ($type=='min')?'_minPos':'_maxPos';
        if ($this->$minPos!==null)
            return $this->$minPos;

        $cells = $this->cells;

        if ($type=='min')
        {
            $resMinPos = $currPos = 0;
            $prevName = 'prev';
            $nextName = 'next';
            $next = 1;
            $groupEnd = 'groupEnd';
            $groupStart = 'groupStart';
            $nextGroupIsEmpty = 'nextGroupIsEmpty';

            $max = 'max';
            $minPos = '_minPos';
            $maxPos = '_maxPos';
            $begNumInd = 0;
            $end = 'end';
            $start = 'start';
            $condPos = function($maxPos,$groupEnd){return $maxPos>$groupEnd;};
            $condNumBefore = function($i,$ind){return $i<$ind;};
            $condNumAfter = function($i,$ind){return $i<=$ind;};
        }
        elseif ($type=='max')
        {
            $resMinPos = $currPos = $cells->count-1;
            $prevName = 'next';
            $nextName = 'prev';
            $next = -1;
            $groupEnd = 'groupStart';
            $groupStart = 'groupEnd';
            $nextGroupIsEmpty = 'prevGroupIsEmpty';

            $max = 'min';
            $minPos = '_maxPos';
            $maxPos = '_minPos';
            $begNumInd = $this->numbers->count-1;
            $end = 'start';
            $start = 'end';
            $condPos = function($maxPos,$groupEnd){return $maxPos<$groupEnd;};
            $condNumBefore = function($i,$ind){return $i>$ind;};
            $condNumAfter = function($i,$ind){return $i>=$ind;};
        }

        if ($this->$prevName!==null)
            $resMinPos = $currPos = $this->$prevName->getPos($type) + $next * ($this->$prevName->length + 1);
            //$resMinPos = $currPos = $cells->getNextFullPosToRight($this->$prevName->getPos($type), $this->$prevName->length);

        if ($cells[$resMinPos]->isFull()) {
            $maxLength = 0;
            for ($i = $begNumInd; $condNumBefore($i, $this->_ind); $i += $next) {
                $maxLength = max($maxLength, $this->numbers[$i]->length);
            }

            $group = $cells[$resMinPos]->group;
            if ($group->length > $maxLength) {
                $this->$minPos = $resMinPos;
                $groupEnd = $group->$start+$next*($this->_length-1);
                if ($condPos($this->getPos($max), $groupEnd))
                    $this->$maxPos = $groupEnd;
            }
        }

        while(-1<$currPos AND $currPos<$cells->count AND $this->$minPos===null)
        {
            //если текущая клетка - крестик
            if ($cells[$currPos]->isEmpty()) {
                //смещаем указатели в следующую группу
                $resMinPos = $currPos = $cells[$currPos]->$groupEnd + $next;
            }
            //иначе если текущая клетка не заполнена
            elseif ($cells[$currPos]->isUnknown()) {
                //определяем конечную позицию незаполненной группы
                $endUnknownPos = $cells[$currPos]->$groupEnd;
                //определяем расстояние от текущей пока что результирующей позиции до конечной позиции текущей группы
                $dist = $cells[$resMinPos]->getDist($endUnknownPos);
                //если расстояние больше текущего числа или
                if ($dist > $this->_length OR
                    //расстояние равно текущему числу и следующая группа крестик или конец
                    $dist == $this->_length AND $cells[$currPos]->$nextGroupIsEmpty)
                        //считаем последеняя пока что результирующая позиция, окончательно ей являестя
                        $this->$minPos = $resMinPos;
                else
                    //иначе смещаем указатель к следующей группе
                    $currPos = $cells[$currPos]->$groupEnd + $next;
            }
            //иначе если текущая клетка закрашена
            elseif ($cells[$currPos]->isFull()) {
                //если длина текущей группы больше текущего числа
                if ($cells[$currPos]->groupLength > $this->_length) {
                    //смещаем указатели на 2 позиции вправо, мысленно полагая, что следующая клетка крестик, а потом идет разукрашенная
                    $resMinPos = $currPos = $cells[$currPos]->$groupEnd + $next*2;
                }
                //если длина текущей группы равно текущему числу
                elseif ($cells[$currPos]->groupLength == $this->_length) {
                    //считаем последеняя пока что результирующая позиция, окончательно ей являестя
                    $resMinPos = $cells[$currPos]->$groupStart;
                    $this->$minPos = $resMinPos;
                }
                //если длина текущей группы меньше текущего числа
                elseif ($cells[$currPos]->groupLength < $this->_length) {
                    //определяем конечную позицию текущей группы
                    $endFullPos = $cells[$currPos]->$groupEnd;
                    //определяем расстояние от текущей пока что результирующей позиции до конечной позиции текущей группы
                    $dist = $cells[$resMinPos]->getDist($endFullPos);

                    //если расстояние меньше текущего числа
                    if ($dist < $this->_length)
                        //иначе смещаем указатель к следующей группе
                        $currPos = $cells[$currPos]->$groupEnd + $next;
                    //иначе если расстояние равно текущему числу
                    elseif ($dist == $this->_length)
                        //считаем, что последняя, пока что, результирующая позиция, окончательно ей являестя
                        $this->$minPos = $resMinPos;
                    //иначе если расстояние больше текущего числа
                    elseif ($dist > $this->_length)
                    {
                        //если результирующая позиция находится в незаполненной клетке
                        if ($cells[$resMinPos]->isUnknown())
                            //считаем, что она окончательно является результирующей
                            $resMinPos = $endFullPos - $next*($this->_length - 1);
                        //иначе если  результирующая позиция находится в закрашенной клетке
                        elseif ($cells[$resMinPos]->isFull())
                            //смещаем её на 2 позиции вправо, мысленно полагая, что следующая клетка крестик, а потом идет разукрашенная
                            $resMinPos = $cells[$resMinPos]->$groupEnd + $next*2;
                    }
                }
            }
        }

        $maxLength = 0;
        if ($this->$minPos!==null)
        {
            for ($i=$begNumInd; $condNumAfter($i,$this->_ind); $i+=$next) {
                $maxLength = max($maxLength, $this->numbers[$i]->length);
            }

            $group = $cells[$this->$minPos]->group;
            while ($group)
            {
                if ($group->isFull())
                {
                    if ($group->length>$maxLength)
                    {
                        if($condPos($this->getPos($max),$group->$end))
                            $this->$maxPos = $group->$end-2*$next;
                    }
                    break;
                }
                $group = $group->$nextName;
            }
        }

        return $this->$minPos;
    }

    public function setMinPos($pos)
    {
        if ($this->_minPos===null)
            $this->_minPos = $this->getPos('min');

        if ($this->_minPos<$pos)
        {
            $this->_minPos = ($pos<0)?0:$pos;
            $this->line->isChange = true;
        }

        if ($this->_minPos>$this->cells->count-$this->_length)
            $this->_minPos = $this->cells->count-$this->_length;

        //смещаем позицию левой границы вправо если она находится там, где крестик
        if ($this->cells[$this->_minPos]->isEmpty())
            $this->setMinPos($this->cells[$this->_minPos]->group->end+1);
    }

    public function setMaxPos($pos)
    {
        if ($this->_maxPos===null)
            $this->_maxPos = $this->getPos('max');

        if ($this->_maxPos>$pos)
        {
            $this->_maxPos = ($pos>$this->cells->count-1)?$this->cells->count-1:$pos;
            $this->line->isChange = true;
        }

        if ($this->_maxPos<$this->_length-1)
            $this->_maxPos = $this->_length-1;

        //смещаем позицию правой границы влево если она находится там, где крестик
        if ($this->cells[$this->_maxPos]->isEmpty())
            $this->setMaxPos($this->cells[$this->_maxPos]->group->start-1);
    }

    public function setBound()
    {
        //определяем границу клеток в которых возможно наличие разукрашенных клеток, соответствующие только одному текущему числу
        $beg = 0;
        if ($this->_prev!==null)
            $beg = ($this->_prev->getPos('max')<$this->getPos('min'))?$this->getPos('min'):$this->_prev->getPos('max')+1;
        else
            $this->getPos('min');

        $end = $this->cells->count-1;
        if ($this->_next!==null)
            $end = ($this->getPos('max')<$this->_next->getPos('min'))?$this->getPos('max'):$this->_next->getPos('min')-1;
        else
            $this->getPos('max');

        $fullMinPos = $fullMaxPos = null;
        //пытаемся на этой границе найти закрашенные клетки
        for ($pos = $beg; $pos<=$end; $pos++)
        {
            if ($this->cells[$pos]->isFull())
            {
                if ($fullMinPos===null)
                    $fullMinPos=$pos;
                $fullMaxPos = $pos;
            }
        }

        //если нашли хотя бы одну закрашенную клетку,
        if ($fullMinPos!==null)
        {
            //уменьшаем возможную границу
            $this->setMinPos($fullMaxPos-$this->_length+1);
            $this->setMaxPos($fullMinPos+$this->_length-1);
        }
    }
	
    //определяет границы, где точно находятся разукрашенные клетки
    private function getRealBound(): ?array
    {
        $minPos = $this->getPos('min');
        $maxPos = $this->getPos('max');
        if (($maxPos-$minPos+1)>=(2*$this->_length))
            return null;

        return [
            'min'=>$this->getPos('max') - $this->_length + 1,
            'max'=>$this->getPos('min') + $this->_length - 1
        ];
    }
    
    //закрашивает клетки по числу основываясь на максимально и минимально возможном положении закрашенной группы
    public function setFullCellsByBound()
    {
        $realBound = $this->getRealBound();
        if ($realBound===null)
            return;

        $this->cells->setFullStates($realBound['min'],$realBound['max']);
    }

    //заполняет клетки крестиком по максимально-возможному положению предыдущего номера и
    //минимально-возможному положению текущего номера
    public function setEmptyCellsByBound()
    {
        //определяем начальную позицию, от которой клетки будут помечены крестиками
        $emptyBegPos = 0;
        if ($this->prev!==null)
            //определяем начальную позицию по конечной позиции предыдущего числа
            $emptyBegPos = $this->prev->getPos('max')+1;

        //определяем конечную позицию, до которой клетки будут помечены крестиками
        //по начальной позиции текущего числа
        $emptyEndPos = $this->getPos('min')-1;

        //заполняем клетки крестиками
        $this->cells->setEmptyStates($emptyBegPos,$emptyEndPos);

        //если текущее число последнее
        if ($this->next===null)
        {
            //опять здесь же определяем начальную и конечную позицию клеток, которые будут заполнены крестиками
            $emptyBegPos = $this->getPos('max')+1;
            $emptyEndPos = $this->cells->count-1;
            $this->cells->setEmptyStates($emptyBegPos,$emptyEndPos);
        }
    }
    
    public function printBound()
    {
        echo $this->_ind.' => '.$this->_length.': '.$this->getPos('min').'-'.$this->getPos('max');
        echo '<br>';
    }
}