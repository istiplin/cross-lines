<?php
namespace models;

use \sys\BaseObject;

//класс для работы с числом, характеризующий количество разукрашенных соседних клеток в строке
class Number extends BaseObject
{
	private $_numbers;
	private $_line;
	public $cells;
        
	private $_length;		//показывает сколько должно быть разукрашено клеток подряд в строке
		
    private $_minPos;				//минимально возможная координата, где начинается разукрашенная клетка
    private $_maxPos;				//максимально возможная координата, где заканчивается разукрашенная клетка
	private $_isResolve = false;	//показывает полностью ли закрашен блок, соответствующий этому числу

    private $_ind;			//номер числа
    private $_prev;			//указатель на предыдущее число
    private $_next;			//указатель на следующее число

    private $_isOneGroup = false;   //число соответствует одной разукрашенной групее

    public function __construct(Numbers $numbers,$length,$ind,$prev=null)
    {
		$this->init($numbers,$prev);
		
		$this->_length = $length;
        $this->_ind = $ind;
    }
	
	public function init(Numbers $numbers,$prev)
	{
		$this->setNumbers($numbers);
		$this->setPrev($prev);
	}
	
	public function setNumbers(Numbers $value)
	{
		$this->_numbers = $value;
		$this->_line = $value->getLine();
	}
	
	public function getLength()
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
		//устанавливаем ссылку на предыдущий объект текущего класса
		$this->_prev = $prev;
		
		if ($prev!==null)
			//в предыдущем объекте текущего класса устанавливаем ссылку на текущий объект
			$prev->setNext($this);
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

        $resMinPos = 0;
        $prev = 'prev';
        $step = 1;
        $direction = 'right';

        if ($type=='max')
        {
            $resMinPos = $cells->count-1;
            $prev = 'next';
            $step = -1;
            $direction = 'left';
        }

        if ($this->$prev!==null)
            $resMinPos = $this->$prev->getPos($type) + $step * ($this->$prev->_length + 1);

        return $this->$minPos = $cells->getFullBegPos($resMinPos, $this->_length, $direction);
    }

    public function setPos($type,int $pos)
    {
        $k=1;
        $_minPos = '_minPos';
        $end = 'end';
        $cellsBegPos = 0;
        $maxMinPos = $this->cells->count-$this->_length;    //максимально-левая позиция
        $_next = '_next';
        $direction = 'right';
        if ($type=='max')
        {
            $k=-1;
            $_minPos = '_maxPos';
            $end = 'start';
            $cellsBegPos = $this->cells->count-1;
            $maxMinPos = $this->_length-1;                  //минимально-правая позиция
            $_next = '_prev';
            $direction = 'left';
        }
        
        //если позиция не определена
        if ($this->$_minPos===null)
            //определяем её
            $this->$_minPos = $this->getPos($type);

        //если позиция, на которую мы хотим поменять текущую, находится правее текущей
        if ($k*$this->$_minPos<$k*$pos)
        {
            //меняем её
            $pos = $this->cells->getFullBegPos($pos, $this->_length, $direction);
            $this->$_minPos = ($k*$pos<$k*$cellsBegPos)?$cellsBegPos:$pos;
            //$this->_line->isChange = true;
            $this->_line->isChangeByNumbers = true;
        }

        //если левая позиция($this->$_minPos) больше максимально-левой позиции($maxMinPos)
        if ($k*$this->$_minPos>$k*$maxMinPos)
            //меняем её
            $this->$_minPos = $maxMinPos;

        //смещаем позицию левой границы вправо если она находится там, где крестик
        if ($this->cells[$this->$_minPos]->isEmpty())
            $this->setPos($type,$this->cells[$this->$_minPos]->group->$end+$k);
        
        //меняем границу для следующего числа
        if ($this->$_next!==null AND $k*$this->$_next->getPos($type)<$k*($this->$_minPos+$k*($this->_length+1)))
            $this->$_next->setPos($type,$this->$_minPos+$k*($this->_length+1));
    }

	public function clearBound()
	{
		$this->_maxPos = null;
		$this->_minPos = null;
	}
	
    public function setBound()
    {
		if ($this->_isResolve)
			return;
			
        //определяем границу клеток в которых возможно наличие разукрашенных клеток, соответствующие только одному текущему числу
        $beg = 0;
        if ($this->_prev!==null)
            $beg = ($this->_prev->getPos('max')<$this->getPos('min'))?$this->getPos('min'):$this->_prev->getPos('max')+1;

        $end = $this->cells->count-1;
        if ($this->_next!==null)
            $end = ($this->getPos('max')<$this->_next->getPos('min'))?$this->getPos('max'):$this->_next->getPos('min')-1;

		if ($end - $beg + 1 == $this->_length)
			return;
			
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
            $this->setPos('min',$fullMaxPos-$this->_length+1);
            $this->setPos('max',$fullMinPos+$this->_length-1);
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
		if ($this->_isResolve)
			return;
			
        $realBound = $this->getRealBound();
        if ($realBound===null)
            return;

        $this->cells->setFullStates($realBound['min'],$realBound['max']);
		
		if ($this->_maxPos - $this->_minPos + 1 == $this->_length)
			$this->_isResolve = true;
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