<?php
namespace models;

use \sys\BaseObject;
use \sys\TArrayAccess;

//класс для работы с группами клеток
class Groups extends BaseObject implements \ArrayAccess
{
    use TArrayAccess;
    
    private $_cells;
    private $_list;
    private $_count=0;

    public function __construct(Cells $cells)
    {
        $this->_cells = $cells;
        $this->setList();
    }

    public function getCells(): Cells
    {
        return $this->_cells;
    }

    public function getList(): array
    {
        if ($this->_list===null)
            $this->setList();
        return $this->_list;
    }
    
    public function getNumbers(): Numbers
    {
        return $this->_cells->line->numbers;
    }

    public function getCount(): int
    {
        return $this->_count;
    }

    private function setList()
    {
        if ($this->_list!==null)
            return;

        $group = null;
        $this->_count = 0;
        $this->_list=[];
        $cells = $this->_cells;
        $prevState = null;
        for($i=0; $i<$cells->count; $i++)
        {
            $currState = $cells[$i]->state;
            //если предыдущее состояние не равно текущему
            if ($prevState!==$currState) {
                //создаем новую группу
                $group = EmptyGroup::initial($this, $currState, $i, $this->_count, $group);

                //и заносим его в список
                $this->_list[$this->_count] = $group;
                $this->_count++;
                $prevState = $currState;
            }

            //в текущей клетке делаем ссылку на текущую группу
            $cells[$i]->group = $group;

            //если текущая клетка последняя или следующее состояние клетки другое
            if ($cells[$i]->next===null OR $currState!==$cells[$i]->next->state)
                //текущей группе задаем последнюю позицию текущей клетки
                $group->end = $i;
        }
        
    }

    private function setGroupNumbers()
    {
        for($i=0; $i<$this->_count; $i++)
        {
            if ($this[$i]->isFull())
                $this[$i]->setGroupNumbers();
        }
    }
    
    private function deleteGroupNumbers()
    {
        $this->setGroupNumbers();

        for($i=0; $i<$this->_count; $i++)
        {
            if ($this[$i]->isFull())
                $this[$i]->deleteGroupNumbers();
        }

        $minInd = null;
        for($i=0; $i<$this->_count; $i++)
        {
            if ($this[$i]->isFull())
                $this[$i]->deleteGroupNumbersOnBound($minInd,'min');
        }

        $maxInd = null;
        for($i=$this->_count-1; $i>-1; $i--)
        {
            if ($this[$i]->isFull())
                $this[$i]->deleteGroupNumbersOnBound($maxInd,'max');
        }
    }

    private function setEmptyCells()
    {
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->setEmptyCells();
    }

    private function setFullCells()
    {
        for($i=0; $i<$this->_count; $i++)
        {
            if ($this->_list[$i]->isFull())
                $this->_list[$i]->setFullCells();
        }
    }

    public function resolve()
    {
        $this->_list = null;
        $this->deleteGroupNumbers();

        $this->setFullCells();
        $this->setEmptyCells();
    }

    public function view()
    {
        for($i=0; $i<$this->count; $i++)
            echo $this[$i]->getView(2);
    }
}