<?php
namespace models;

use \sys\BaseObject;

//класс для работы с полем кроссворда, где оно разгадывается
class Field1 extends BaseObject
{
    public $name;
    
    private $_fieldCells;
    private $_width;
    private $_height;

    public function __construct($horNums,$vertNums,$cellsStrArr=null,$name=null)
    {
        $this->name = $name;
        
        $this->_width = count($vertNums);
        $this->_height = count($horNums);
        
        $this->_fieldCells = new FieldCells($horNums,$vertNums,$cellsStrArr,$name);
    }

    public function getWidth()
    {
        return $this->_width;
    }
	
    public function sizeView()
    {
        return $this->_width.'X'.$this->_height;
    }
	
    //abstract protected function solveLines(): bool;

    public function solve(): bool
    {
        return $this->_fieldCells->solve();
    }

    public function getCells()
    {
        return $this->_fieldCells->getCells();
    }
	
    public function getHorNums()
    {
        return $this->_fieldCells->getHorNums();
    }

    public function getVertNums()
    {
        return $this->_fieldCells->getVertNums();
    }
}