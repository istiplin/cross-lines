<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из строк японского кроссворда
class Line extends BaseObject
{
    private $_field;
	public $crossLines;
	
    private $_numbers;
    private $_cells;
	private $_groups;

    public $isChangeByNumbers = true;
    public $isChangeByGroups = true;
	
    public $isHorizontal;

    public $ind;
	private $_numbersList;
	
    private $_isMirror = false;
	
	private $_unknownCount;

    public function __construct($ind,$numbersList,string $cellsStr,bool $isHorizontal,Field $field=null,$isMirror=false)
    {
		$this->isHorizontal = $isHorizontal;
		$this->_field = $field;
		
        $this->ind = $ind;
        $this->_isMirror = $isMirror;
        if ($this->_isMirror)
        {
            $numbersList = array_reverse($numbersList);
            $cellsStr = strrev($cellsStr);
        }
		$this->_numbersList = $numbersList;
		
		$this->_numbers = new Numbers($numbersList,$this);
		$this->_cells = new Cells($cellsStr,$this);
		
		$this->_groups = $this->_cells->getGroups();
		$this->_numbers->setCells($this->_cells);
    }
	
	public function __clone()
	{	
		$this->_groups = null;
		
		$this->_field = null;
		$this->crossLines = null;
		
		$this->_numbers = new Numbers($this->_numbersList,$this);
		/*
		$this->_numbers = clone $this->_numbers;
		$this->_numbers->setLine($this);
		$this->_numbers->cloneList();
		*/
		
		$this->_cells = new Cells($this->_cells->getData(),$this);
		/*
		$this->_cells = clone $this->_cells;
		$this->_cells->setLine($this);
		$this->_cells->cloneList();
		$this->_cells->cloneGroups();
		*/
		
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
	
	public function getNumbersList()
	{
		return $this->_numbersList;
	}

    private function setEmptyByNoNumbers()
    {
        if ($this->_numbers->count == 0)
            $this->_cells->setEmptyStates(0, $this->_cells->count-1);
    }
	
	public function setUnknownCount($value)
	{
		$this->_unknownCount = $value;
	}
	
    public function getUnknownCount(): int
    {
        return $this->_unknownCount;
    }
    
    public function decrUnknownCount($pos,$oldState,$state)
    {
        $this->_unknownCount--;
		
        if ($this->_unknownCount<0)
		{
			if ($this->isHorizontal)
				$orName = 'Horizontal';
			else
				$orName = 'Vertical';
            throw new \Exception(" Error! state:$oldState->$state, orientation:$orName, line:{$this->_cells->ind}, pos:$pos. this->_unknownCount is bellow zero");
		}
    }
	
	public function resolveByNumbers(): bool
	{
		try{
			//переопределяем список блоков в строке
			$this->_groups->resetList();
			
			//сначала определяем границы для каждого числа в строке
			$this->_numbers->setBounds();
			
			$this->_numbers->resolve();
			
		}
		catch(\Exception $e)
		{
		    //echo $e->getMessage().'<br>';
			return false;
		}
		return true;
	}
	
	public function resolveByGroups(): bool
	{
		try{
			$this->_groups->resolve();
		}
		catch(\Exception $e)
		{
			//echo $e->getMessage().'<br>';
			return false;
		}
		return true;
	}
	
    public function resolveTest():bool
    {
		try{
			//переопределяем список блоков в строке
			$this->_groups->resetList();

			//сначала определяем границы для каждого числа в строке
			$this->_numbers->setBounds();
			
			$this->setEmptyByNoNumbers();
            while($this->isChangeByNumbers OR $this->isChangeByGroups)
			{

				//потом пытаемся разгадать строку, рассматривая на каждое число
				$this->_numbers->resolve();
		
				//затем пытаемся разгадать строку, рассматривая каждый блок однотипных клеток
				$this->_groups->resolve();
			}
			$this->_groups->resolveByClone();
				
			//$this->unsetOrientation();
		}
		catch(\Exception $e)
		{
			//echo $e->getMessage().'<br>';
			return false;
		}
		
		return true;
    }
    
    public function getCellsView()
    {
        $cellsView = $this->_cells->getData();
        if ($this->_isMirror)
            $cellsView = strrev($cellsView);
        return $cellsView;
    }
}