<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из линий японского кроссворда
class Field extends BaseObject
{
    public $name;
    
    private $_horLines = [];
    private $_vertLines = [];
    
    private $_width;
    private $_height;
    
    private $_cells = [];

    public function __construct($horNums,$vertNums,$name=null)
    {
        $this->name = $name;
        
        $this->_width = count($vertNums);
        $this->_height = count($horNums);
        
        $horCells = str_repeat(Cell::UNKNOWN_STATE, $this->_width);
        for ($i=0; $i<$this->_height; $i++)
            $this->_horLines[$i] = new Line($i,$horNums[$i],$horCells,true,$this);
        
        $vertCells = str_repeat(Cell::UNKNOWN_STATE, $this->_height);
        for ($i=0; $i<$this->_width; $i++)
            $this->_vertLines[$i] = new Line($i,$vertNums[$i],$vertCells,false,$this);
        
        for ($i=0; $i<$this->_height; $i++)
            $this->_horLines[$i]->crossLines = &$this->_vertLines;
        
        for ($i=0; $i<$this->_width; $i++)
            $this->_vertLines[$i]->crossLines = &$this->_horLines;
    }
    
    public function getCell($state,$ind,Line $line)
    {
        if ($line->isHorizontal)
        {
            if (!isset($this->_cells[$line->ind][$ind]))
                $this->_cells[$line->ind][$ind] = new Cell($state);
            return $this->_cells[$line->ind][$ind];
        }
        else
        {
            if (!isset($this->_cells[$ind][$line->ind]))
                $this->_cells[$ind][$line->ind] = new Cell($state);
            return $this->_cells[$ind][$line->ind];
        }
    }
    
    public function getWidth()
    {
        return $this->_width;
    }
    
    public function getHeight()
    {
        return $this->_height;
    }
    
    public function sizeView()
    {
        return $this->_width.'X'.$this->_height;
    }
    
    public function resolve()
    {
        $isChange = true;
        while($isChange)
        {
            $isChange = false;
            
            for ($i=0; $i<$this->_height; $i++)
            {
                if (!$this->_horLines[$i]->isChange)
                    continue;
                $isChange = true;
                $this->_horLines[$i]->resolve();
            }
            
            for ($i=0; $i<$this->_width; $i++)
            {
                if (!$this->_vertLines[$i]->isChange)
                    continue;
                $isChange = true;
                $this->_vertLines[$i]->resolve();
            }
        }
    }
    
    public function getView()
    {
        $view = '<br>';
        for ($y=0; $y<$this->_height; $y++)
        {
            for($x=0; $x<$this->_width; $x++)
                $view.=$this->_cells[$y][$x]->state;
            $view.='<br>';
        }
        return $view;
    }

}