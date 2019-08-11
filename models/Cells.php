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

    public function __construct($data,$line)
    {
        $this->_line = $line;
        $this->setList($data);
    }

    private function setList($data)
    {
        $this->_unknownCount=0;
        $elem = null;
        for($i=0; $i<strlen($data); $i++)
        {	
            $state = (int)$data[$i];
            $elem = new Cell($this,$state,$i,$elem);
            $this->_list[$i] = $elem;
            $this->_count++;
            if ($elem->isUnknown())
                $this->_unknownCount++;
        }
    }

    //возвращает начальную позицию следующей зкрашенной группы,
    //находящейся со стороны $direction от предыдущей закрашенной группы.
    //Предыдущая закрашенная группа начинается с позиции $begPos и её длина $fullLength,
    public function getNextFullPosToRight($begPos, $fullLength, $direction)
    {

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