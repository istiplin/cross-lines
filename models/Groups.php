<?php
namespace models;

use \sys\BaseObject;
use \sys\TArrayAccess;

//класс для работы с группами клеток
class Groups extends BaseObject implements \ArrayAccess
{
    use TArrayAccess;
    
	private $_field;
    private $_cells;
	private $_line;
	private $_numbers;
	
    private $_list;
    private $_count=0;

    public function __construct(Cells $cells)
    {
        $this->setCells($cells);
    }
	
	public function __destruct()
	{
		$this->unsetCells();
	}

    public function getList(): array
    {
        if ($this->_list===null)
            $this->setList();
        return $this->_list;
    }
    
	public function setCells(Cells $value)
	{
		$this->_cells = $value;
		$this->_line = $value->getLine();
		$this->_field = $this->_line->getField();
		$this->_numbers = $value->getNumbers();
	}
	
	public function unsetCells()
	{
		$this->_numbers = null;
		$this->_field = null;
		$this->_line = null;
		$this->_cells = null;
	}
	
	
	public function getLine(): Line
	{
		return $this->_line;
	}
	
	public function getCells(): Cells
	{
		return $this->_cells;
	}

    public function getCount(): int
    {
        return $this->_count;
    }

	public function resetList()
	{
		$this->_list=null;
		$this->setList();
	}
	
    private function setList()
    {
        if ($this->_list!==null)
            return;
			
        $elem = null;
        $this->_count = 0;
        $this->_list=[];
        $cells = $this->getCells();
		$cellsCount = $cells->count;
        $prevState = null;
		
        for($i=0; $i<$cellsCount; $i++)
        {
            $currState = $cells[$i]->state;
            //если предыдущее состояние не равно текущему
            if ($prevState!==$currState) {
			
                //создаем новую группу
                $elem = EmptyGroup::initial($this, $currState, $i, $this->_count, $elem);
				
                //и заносим его в список
                $this->_list[$this->_count] = $elem;
                $this->_count++;
                $prevState = $currState;
            }

            //в текущей клетке делаем ссылку на текущую группу
            $cells[$i]->setGroup($elem);
				
            //если текущая клетка последняя или следующее состояние клетки другое
            if ($cells[$i]->getNext()===null OR $currState!==$cells[$i]->getNext()->getState())
                //текущей группе задаем последнюю позицию текущей клетки
                $elem->setEnd($i);
        }
    }
	
    public function unsetList()
    {
        $cells = $this->getCells();
        $cellsCount = $cells->count;
        for($i=0; $i<$cellsCount; $i++)
                $cells[$i]->setGroup(null);

        for ($i=0; $i<$this->_count; $i++)
                $this->_list[$i]=null;
        $this->_list = null;
    }
    
    private function deleteFullGroupNumbers()
    {
        for($i=0; $i<$this->_count; $i++)
        {
            if ($this->_list[$i]->isFull())
                $this->_list[$i]->deleteGroupNumbers();
        }

        $minInd = null;
        for($i=0; $i<$this->_count; $i++)
        {
            if ($this->_list[$i]->isFull())
                $this->_list[$i]->deleteGroupNumbersOnBound($minInd,'min');
        }

        $maxInd = null;
        for($i=$this->_count-1; $i>-1; $i--)
        {
            if ($this->_list[$i]->isFull())
                $this->_list[$i]->deleteGroupNumbersOnBound($maxInd,'max');
        }

    }

    private function deleteUnknownGroupNumbers()
    {
        for($i=0; $i<$this->_count; $i++)
        {
            if ($this->_list[$i]->isFull())
                $this->_list[$i]->deleteUnknownGroupNumbers();
        }
    }
	
    public function setGroupNumbers($isDetail=false)
    {
        for($i=0; $i<$this->_count; $i++)
        {
            if ($this->_list[$i]->isFull())
                $this->_list[$i]->setGroupNumbers();
        }

        //некоторые полученные числа удаляем
        $this->deleteFullGroupNumbers();
        //$this->deleteUnknownGroupNumbers();

        if ($isDetail)
        {
            $this->viewGroupNumbers();
            $this->_numbers->viewBounds();
        }
    }

    public function setEmptyCells()
    {
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->setEmptyCells();
    }

    public function setFullCells()
    {
        for($i=0; $i<$this->_count; $i++)
        {
            $this->_list[$i]->setFullCells();
        }
    }
	
    public function setStateCells($isView=false)
    {
        $this->setFullCells();
        $this->setEmptyCells();

        if ($isView)
            echo $this->_line->getView($isView).' group-<b>RESULT</b><br>';
    }

    public function solve($isView=false, $isDetail=false)
    {
	
		$unknownCount = $this->_line->getUnknownCount() + 1;
		while($this->_line->getUnknownCount()<$unknownCount AND $this->_line->getUnknownCount()>0)
		{
			$unknownCount = $this->_line->getUnknownCount();
			
			$this->resetList();
			
			$this->setGroupNumbers();

			if ($isDetail)
				$this->viewGroupNumbers();
			
			$this->setFullCells();
			$this->setEmptyCells();
			
			if ($isView)
				echo $this->_line->getView(true).' group-<b>RESULT</b><br>';
		}
    }
	
    public function solveByClone()
    {
        for($i=0; $i<$this->_count; $i++)
        {
            if ($this[$i]->isUnknown())
                $this[$i]->solveByClone();
        }
    }

    public function viewGroupNumbers()
    {
        for($i=0; $i<$this->count; $i++)
            echo $this[$i]->getView();
    }
	
	public function view()
	{
		$this->viewGroupNumbers();
		echo $this->_line->getView().' group-<b>RESULT</b><br>';
	}
}