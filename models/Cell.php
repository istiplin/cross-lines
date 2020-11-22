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

    private $_x;
    private $_y;
    private $_id;

    public $unknownCountByFull;
    public $unknownCountByEmpty;

    public function __construct(int $x, int $y, Field $field = null)
    {
        $this->_x = $x;
        $this->_y = $y;
        if ($field===null)
                $this->_id = $x;
        else
        {
                $this->_field = $field;
                $this->_id = $y*$field->getWidth()+$x;
        }
    }
	
	public function getX()
	{
		return $this->_x;
	}
	
	public function getY()
	{
		return $this->_y;
	}
	
	public function getId()
	{
		return $this->_id;
	}

    public function setCellData(CellData $cellData)
    {
        //if ($isHorizontal)
            $this->_horOr = $cellData;
        //else
        //	$this->_vertOr = $cellData;
    }
	
    public function getState()
    {
        return $this->_state;
    }
	
    public function setFull()
    {
        $this->setState(self::FULL_STATE);
    }

    public function setEmpty()
    {
        $this->setState(self::EMPTY_STATE);
    }
	
    public function setUnknown()
    {
        $this->setState(self::UNKNOWN_STATE);
    }
	
    public function setState($state)
    {
        //если мы хотим заштриховать клетку а ней крестик, то это ошибка
        if ($this->isFull($state) AND $this->isEmpty())
            throw new \Exception('x:'.$this->_x.' y:'.$this->_y.' is empty instead full');
		
        //если мы хотим поставить крастик, а клетка заштрихована, то это ошибка
        if ($this->isEmpty($state) AND $this->isFull())
            throw new \Exception($this->_x.' '.$this->_y.' is full instead empty');	
		
        //если мы хотим поменять неизвестное состояние клетки на известное, то меняем
        if (!$this->isUnknown($state) AND $this->isUnknown()){
            $this->_state = $state;
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
	
    public function upIsEmpty() : bool
    {
        return $this->_vertOr->prevIsEmpty();
    }

    public function downIsEmpty() : bool
    {
        return $this->_vertOr->nextIsEmpty();
    }

    public function rightIsEmpty() : bool
    {
        return $this->_horOr->nextIsEmpty();
    }

    public function leftIsEmpty() : bool
    {
        return $this->_horOr->prevIsEmpty();
    }

}