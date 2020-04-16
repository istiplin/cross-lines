<?php
namespace models;

use \sys\BaseObject;

//класс для работы с клетками, по которым строится рисунок
class Cell extends BaseObject
{
    const UNKNOWN_STATE = '0';
    const FULL_STATE = '1';
    const EMPTY_STATE = '2';

	private $_state=self::UNKNOWN_STATE;    //состояние клетки
	
	private $_field;
	
	private $_horOr;	//объект, в котором хранятся данные о горизонтальной ориентации текущего объекта клетки
	private $_vertOr;	//объект, в котором хранятся данные о вертикальной ориентации текущего объекта клетки
	
	public $fieldInd=null;
	
	public $unknownCountByFull;
	public $unknownCountByEmpty;

    public function __construct(?Field $field)
    {
		$this->_field = $field;
    }
	
	public function setCellData(CellData $cellData,bool $isHorizontal)
	{
		if ($isHorizontal)
			$this->_horOr = $cellData;
		else
			$this->_vertOr = $cellData;
	}
	
    public function getState()
    {
        return $this->_state;
    }
	
    public function setState($state)
    {
		if ($this->isFull($state) AND $this->isEmpty())
            throw new \Exception($this->cells->getData().' error in line:'.$this->getCells()->getLine()->ind.' pos '.$this->getInd().' is empty instead full');
			
        if ($this->isEmpty($state) AND $this->isFull())
            throw new \Exception($this->cells->getData().' error in line:'.$this->getCells()->getLine()->ind.' pos '.$this->getInd().' is full instead empty');	
		
        if (!$this->isUnknown($state) AND $this->isUnknown())
        {
			if ($this->_field)
				$this->_field->deleteUnknownCell($this->fieldInd);

			$oldState = $this->_state;
			$this->_state = $state;
			$this->setIsChange($oldState,$state);
        }
    }
	
	private function setIsChange($oldState,$state)
	{
		if ($this->_horOr)
			$cellData = $this->_horOr;
		else
			$cellData = $this->_vertOr;
			
		$line = $cellData->getLine();
		$ind = $cellData->getInd();
		
        $line->isChangeByNumbers = true;
        $line->isChangeByGroups = true;
		$line->decrUnknownCount($ind,$oldState,$state);
		
		if ($line->crossLines)
		{
			$line = $line->crossLines[$ind];
			$ind = $line->ind;
			
            $line->isChangeByNumbers = true;
            $line->isChangeByGroups = true;
			$line->decrUnknownCount($ind,$oldState,$state);
		}
	}
	
    public function getFull()
    {
        return self::FULL_STATE;
    }
    
    public function getEmpty()
    {
        return self::EMPTY_STATE;
    }

    public function isUnknown($state=null):bool
    {
		if ($state === null)
			$state = $this->_state;
        return $state===self::UNKNOWN_STATE;
    }

    public function isFull($state=null):bool
    {
		if ($state === null)
			$state = $this->_state;
        return $state===self::FULL_STATE;
    }

    public function isEmpty($state=null):bool
    {
		if ($state === null)
			$state = $this->_state;
        return $state===self::EMPTY_STATE;
    }
	
    public function setFull()
    {
        $this->setState(self::FULL_STATE);
    }

    public function setEmpty()
    {
        $this->setState(self::EMPTY_STATE);
    }

}