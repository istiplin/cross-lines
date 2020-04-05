<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из линий японского кроссворда
class Field// extends BaseObject
{
    public $name;
    public $duration;
	
	public $currOrName='';
	
    private $_horLines = [];
    private $_vertLines = [];
    
    private $_width;
    private $_height;
    
    private $_cellsArr = [];

    public function __construct($horNums,$vertNums,$name=null)
    {
        $this->name = $name;
        
        $this->_width = count($vertNums);
        $this->_height = count($horNums);
        
        $horCells = str_repeat(Cell::UNKNOWN_STATE, $this->_width);
        for ($i=0; $i<$this->_height; $i++)
		{
			$this->currOrName = '_horOr';
            $this->_horLines[$i] = new Line($i,$horNums[$i],$horCells,true,$this);
			$this->currOrName = '';
		}
        
        $vertCells = str_repeat(Cell::UNKNOWN_STATE, $this->_height);
        for ($i=0; $i<$this->_width; $i++)
		{
			$this->currOrName = '_vertOr';
            $this->_vertLines[$i] = new Line($i,$vertNums[$i],$vertCells,false,$this);
			$this->currOrName = '';
		}
        
        for ($i=0; $i<$this->_height; $i++)
            $this->_horLines[$i]->crossLines = &$this->_vertLines;
        
        for ($i=0; $i<$this->_width; $i++)
            $this->_vertLines[$i]->crossLines = &$this->_horLines;
    }
    
    public function getCell($state,$ind,Line $line): Cell
    {
        if ($line->isHorizontal)
        {
            if (!isset($this->_cellsArr[$line->ind][$ind]))
                $this->_cellsArr[$line->ind][$ind] = new Cell($state,$ind,$line->ind,$this);
            return $this->_cellsArr[$line->ind][$ind];
        }
        else
        {
            if (!isset($this->_cellsArr[$ind][$line->ind]))
                $this->_cellsArr[$ind][$line->ind] = new Cell($state,$line->ind,$ind,$this);
            return $this->_cellsArr[$ind][$line->ind];
        }
    }
    
    public function sizeView()
    {
        return $this->_width.'X'.$this->_height;
    }
    
    public function resolve()
    {
		$time = microtime(true);
        $isChange = true;
        while($isChange)
        {
            $isChange = false;
            
            for ($i=0; $i<$this->_height; $i++)
            {
                if (!$this->_horLines[$i]->isChange)
                    continue;
                $isChange = true;
				$this->currOrName = '_horOr';
                $this->_horLines[$i]->resolve();
				$this->currOrName = '';
            }
            
            for ($i=0; $i<$this->_width; $i++)
            {
                if (!$this->_vertLines[$i]->isChange)
                    continue;
                $isChange = true;
				$this->currOrName = '_vertOr';
                $this->_vertLines[$i]->resolve();
				$this->currOrName = '';
            }
        }
		$this->duration = microtime(true) - $time;
    }
    
    public function getView()
    {
        $view = '<br>';
        for ($y=0; $y<$this->_height; $y++)
        {
            for($x=0; $x<$this->_width; $x++)
                $view.=$this->_cellsArr[$y][$x]->state;
            $view.='<br>';
        }
		$view.='Длительность: '.$this->duration.' сек.';
        return $view;
    }

}