<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из строк японского кроссворда
class Line extends BaseObject
{
    public $isChange = true;
    
    public $crossLines=null;
    public $isHorizontal;
	
    private $_field;
	
    private $_numbers;
    private $_cells;
	private $_groups;
	
    public $ind;
	
    private $_isMirror = false;

    public function __construct($ind,$numbers,string $cellsStr,bool $isHorizontal,$field,$isMirror=false)
    {
        $this->ind = $ind;
        $this->_isMirror = $isMirror;
        if ($this->_isMirror)
        {
            $numbers = array_reverse($numbers);
            $cellsStr = strrev($cellsStr);
        }
        $this->isHorizontal = $isHorizontal;
        $this->_field = $field;
		
		$this->_numbers = new Numbers($numbers,$this);
		
		$this->_cells = new Cells($cellsStr,$this);
		$this->_groups = $this->_cells->getGroups();
		
		$this->_numbers->setCells($this->_cells);
    }
	
	public function getField(): ?Field
	{
		return $this->_field;
	}
	
	public function getCells(): Cells
	{
		return $this->_cells;
	}
	
	public function getNumbers(): Numbers
	{
		return $this->_numbers;
	}

    private function setEmptyByNoNumbers()
    {
        if ($this->_numbers->count == 0)
            $this->_cells->setEmptyStates(0, $this->_cells->count-1);
    }
    
    public function resolve($withView=false,$withDetail=false)
    {
		//переопределяем список блоков в строке
		$this->_groups->resetList();
        
		//сначала определяем границы для каждого числа в строке
        $this->_numbers->setBounds();
        
        if ($withView)
            $this->_cells->view();
        
        if ($withDetail)
            $this->_numbers->printBounds();
        
        $this->setEmptyByNoNumbers();
        while ($this->isChange)
        {
            $this->isChange = false;

            if ($this->_cells->unknownCount>0) {
                //потом пытаемся разгадать строку, опираясь на каждое число
                $this->_numbers->resolve();
                
                if ($withDetail)
                {
                    $this->_cells->view();
                    $this->_numbers->printBounds();
                }
            }

            if ($this->_cells->unknownCount>0) {
                if ($withDetail)
                    $this->_cells->view();
                //затем пытаемся разгадать строку, опираясь на каждый блок из однотипных клеток
                $this->_groups->resolve();
                
                if ($withDetail)
                    $this->_groups->view();
            }
        }
        
        if ($withView)
            $this->_cells->view();
    }
    
    public function getCellsView()
    {
        $cellsView = $this->_cells->getView();
        if ($this->_isMirror)
            $cellsView = strrev($cellsView);
        return $cellsView;
    }
}