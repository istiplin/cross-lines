<?php
namespace models;

use \sys\BaseObject;
use \sys\TArrayAccess;

//класс для работы с клетками, по которым строится рисунок
class Cells extends BaseObject implements \ArrayAccess
{
    use TArrayAccess;
    
    private $_line;
	private $_field;
	private $_groups;
	
    private $_list;
    private $_count=0;

	private $_data;

    public function __construct(string $data,Line $line)
    {
		$this->_data = $data;
        $this->setLine($line);
        $this->resetList();
		$this->setGroups();
    }
	
	public function setLine(Line $value)
	{
		$this->_line = $value;
		$this->_field = $value->getField();
	}
	
	public function getLine(): Line
	{
		return $this->_line;
	}
	
	public function getField(): Field
	{
		return $this->_field;
	}
	
	private function setGroups()
	{
		if ($this->_groups!==null)
			throw new \Exception('Error! '.__METHOD__.' $this->_groups is not null');
		$this->_groups = new Groups($this);
	}
	
	public function getGroups(): Groups
	{
		return $this->_groups;
	}
	
	public function getData($isViewChange=false,string $delimiter = '')
	{
		if ($this->_list===null)
			return null;
			
		$oldData = $this->_data;
		$this->_data = '';
        $data='';
        for ($i = 0; $i<$this->_count; $i++)
		{
			if (!$isViewChange OR $oldData[$i]==$this->_list[$i]->getState())
				$data.=$this->_list[$i]->getState();
			else
				$data.='<b>'.$this->_list[$i]->getState().'</b>';
			
			$this->_data.=$this->_list[$i]->getState();
			
			if (strlen($delimiter) AND ($i+1)%10==0)
				$data.= $delimiter;
		}
        return $data;
	}
	
    public function getElem($ind):CellData
    {
		if (array_key_exists($ind,$this->_list))
			return $this->_list[$ind];
	
		$line = $this->getLine();
        if ($this->_field!==null)
            $cell = $this->_field->getCell($ind,$line);
		
		//для юнит-тестов
		elseif ($this->_line->isHorizontal)
			$cell = new Cell($ind,$line->ind);
		//для юнит-тестов
		else
			$cell = new Cell($ind,$line->ind);
			
		return new CellData($ind,$cell,$this);
    }
    
	private function resetList()
	{
		$this->_list = null;
		$this->setList();
	}
	
    private function setList()
    {
		if ($this->_list!==null)
			throw new \Exception('Error! '.__METHOD__.' $this->_list is not null');
	
		$this->_count = strlen($this->_data);
        $this->_line->setUnknownCount($this->_count);
		$prevElem = null;
		$this->_list = [];
        for($i=0; $i<$this->_count; $i++)
		{
            $elem = $this->getElem($i);
			$elem->setState($this->_data[$i]);
			$elem->setPrev($prevElem);
			$prevElem = $elem;
			
			$this->_list[$i] = $elem;
		}
		$prevElem->setNext(null);
    }
	
	public function cloneList()
	{
		if ($this->_list===null)
			throw new \Exception('Error! '.__METHOD__.' $this->_list is null');
		
		$prevElem = null;
		for($i=0; $i<$this->_count; $i++)
		{
			$elem = clone $this->_list[$i];
			$elem->setField($this->_field);
			$elem->setCells($this);
			$elem->setPrev($prevElem);
			$prevElem = $elem;
			$this->_list[$i] = $elem;
		}
		$prevElem->setNext(null);
	}

    //возвращает итоговую длину возможной закрашенной группы,
    //начиная с позиции $fullBegPos, пытаясь пройти  расстояние длиной $fullLength
    //в направлении $direction
    public function getFullLength($fullBegPos, $fullLength, $direction): int
    {
        if ($fullLength==0)
            throw new \Exception('$fullLength is 0');

        if ($this->_list[$fullBegPos]->isEmpty())
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

        if ($this->_list[$currPos]->isFull())
        {
            if ($currPos !== $this->_list[$currPos]->$groupStart)
                throw new \Exception("currPos=$currPos is not $groupStart = {$this->_list[$currPos]->$groupStart} (fullBegPos=$fullBegPos, fullLength=$fullLength, $direction)");
            if ($fullLength < $this->_list[$currPos]->groupLength)
                return 0;
            if ($fullLength == $this->_list[$currPos]->groupLength)
                return $fullLength;
        }

        while(true) {

            if ($this->_list[$currPos]->isFull()) {
                $resFullLength += ($step*($this->_list[$currPos]->$groupEnd - $currPos) + 1);
            }
            elseif ($this->_list[$currPos]->isUnknown()) {
                $resFullLength += ($step*($this->_list[$currPos]->$groupEnd - $currPos) + 1);
                if ($resFullLength > $fullLength)
                    return $fullLength;
                if ($this->_list[$currPos]->$nextGroupIsEmpty)
                    return $resFullLength;
                if ($resFullLength == $fullLength)
                    return $resFullLength - 1;

                $nextLength = $this->_list[$currPos]->group->$next->length;
                if ($fullLength < $resFullLength + $nextLength)
                    return $resFullLength - 1;

            }
            if ($this->_list[$currPos]->$nextGroupIsEmpty)
                break;
            $currPos = $this->_list[$currPos]->$groupEnd + $step;
        }

        return $resFullLength;
    }

    //определяет позицию клетки, с которой начинается и может поместиться закрашенный блок длинной $fullLength,
    //начиная поиск с позиции $fullBegPos в направлении $direction
    public function getFullBegPos($fullBegPos, $fullLength, $direction): int
    {
		$fullBegPos1 = $fullBegPos;
	
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
            if ($this->_list[$fullBegPos]->isUnknown() AND $this->_list[$fullBegPos]->$prevIsFull())
            {
                $fullBegPos+=$next;
            }
        }
        
        $currPos = $fullBegPos;
        
        while(-1<$currPos AND $currPos<$this->_count)
        {
            //если текущая клетка - крестик
            if ($this->_list[$currPos]->isEmpty())
                //смещаем указатели в следующую группу
                $fullBegPos = $currPos = $this->_list[$currPos]->$groupEnd + $next;
            //иначе если текущая клетка не заполнена
            elseif ($this->_list[$currPos]->isUnknown()) {
                //определяем конечную позицию незаполненной группы
                $endUnknownPos = $this->_list[$currPos]->$groupEnd;
                //определяем расстояние от текущей пока что результирующей позиции до конечной позиции текущей группы
                $dist = $this->_list[$fullBegPos]->getDist($endUnknownPos);
                //если расстояние больше текущего числа или
                if ($dist > $fullLength OR
                    //расстояние равно текущему числу и следующая группа крестик или конец
                    $dist == $fullLength AND $this->_list[$currPos]->$nextGroupIsEmpty)
                    //считаем последеняя пока что результирующая позиция, окончательно ей являестя
                    return $fullBegPos;
                else
                    //иначе смещаем указатель к следующей группе
                    $currPos = $this->_list[$currPos]->$groupEnd + $next;
            }
            //иначе если текущая клетка закрашена
            elseif ($this->_list[$currPos]->isFull()) {
                //если длина текущей группы больше текущего числа
                if ($this->_list[$currPos]->groupLength > $fullLength)
                    //смещаем указатели на 2 позиции вправо, мысленно полагая, что следующая клетка крестик, а потом идет разукрашенная
                    $fullBegPos = $currPos = $this->_list[$currPos]->$groupEnd + $next*2;
                //если длина текущей группы равно текущему числу
                elseif ($this->_list[$currPos]->groupLength == $fullLength)
                    //считаем последняя пока что результирующая позиция, окончательно ей являестя
                    return $this->_list[$currPos]->$groupStart;
                //если длина текущей группы меньше текущего числа
                elseif ($this->_list[$currPos]->groupLength < $fullLength) {
                    //определяем конечную позицию текущей группы
                    $endFullPos = $this->_list[$currPos]->$groupEnd;
                    //определяем расстояние от текущей пока что результирующей позиции до конечной позиции текущей группы
                    $dist = $this->_list[$fullBegPos]->getDist($endFullPos);

                    //если расстояние меньше текущего числа
                    if ($dist < $fullLength)
                        //иначе смещаем указатель к следующей группе
                        $currPos = $this->_list[$currPos]->$groupEnd + $next;
                    //иначе если расстояние равно текущему числу
                    elseif ($dist == $fullLength)
                        //считаем, что последняя, пока что, результирующая позиция, окончательно ей являестя
                        return $fullBegPos;
                    //иначе если расстояние больше текущего числа
                    elseif ($dist > $fullLength)
                    {
                        //если результирующая позиция находится в незаполненной клетке
                        if ($this->_list[$fullBegPos]->isUnknown())
                            //считаем, что она окончательно является результирующей
                            $fullBegPos = $endFullPos - $next*($fullLength - 1);
                        //иначе если  результирующая позиция находится в закрашенной клетке
                        elseif ($this->_list[$fullBegPos]->isFull())
                            //смещаем её на 2 позиции вправо, мысленно полагая, что следующая клетка крестик, а потом идет разукрашенная
                            $fullBegPos = $this->_list[$fullBegPos]->$groupEnd + $next*2;
                    }
                }
            }
        }
		throw new \Exception('Error! Line:'.$this->line->ind.' Method '.__METHOD__.' return is null. '."[$fullBegPos1,$fullLength,$direction] {$this->getData()}");
    }

    public function getNumbers(): Numbers
    {
        return $this->_line->numbers;
    }
    
    public function getList(): array
    {
        return $this->_list;
    }
	
    public function getCount(): int
    {
        return $this->_count;
    }
    
    private function setState($state,$start,$end=null)
    {
		if ($end===null)
			$this->_list[$start]->setState($state);
		else
		{
			for ($i=$start; $i<=$end; $i++)
				$this->_list[$i]->setState($state);
		}
    }

    public function setFullStates($start,$end=null)
    {
		$this->setState(Cell::FULL_STATE,$start,$end);
    }
    
    public function setEmptyStates($start,$end=null)
    {
		$this->setState(Cell::EMPTY_STATE,$start,$end);
    }
	
	//определяет границу закрашенных клеток
	public function getFullBound($beg,$end):array
	{
        $fullMinPos = $fullMaxPos = null;
        //пытаемся на этой границе найти закрашенные клетки
        for ($pos = $beg; $pos<=$end; $pos++)
        {
            if ($this->_list[$pos]->isFull())
            {
                if ($fullMinPos===null)
                    $fullMinPos=$pos;
                $fullMaxPos = $pos;
            }
        }
		return [$fullMinPos,$fullMaxPos];
	}
	
	public function setEmpty($ind)
	{	
		if ($ind>=0 AND $ind<$this->_count)
			$this->_list[$ind]->setEmpty();
	}
	
    public function view()
    {
        $this->numbers->view();
        echo ' ';
		echo $this->getData();
        echo '<br>';
    }
}