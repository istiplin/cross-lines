<?php
namespace models;

use \sys\BaseObject;
use \sys\TArrayAccess;

//класс для работы с числами в строке, по которым строится рисунок
class Numbers extends BaseObject implements \ArrayAccess
{
    use TArrayAccess;
    
    private $_line;
	
    private $_list;
    private $_count=0;
	private $_data;
    
    public function __construct($data,Line $line)
    {
		$this->_data = $data;
		$this->setLine($line);
        $this->resetList();
    }
	
	public function setLine(Line $value)
	{
		$this->_line = $value;
	}
    
	private function resetList()
	{
		$this->_list = null;
		$this->setList();
	}
	
    private function setList()
    {
		if ($this->_list!==null)
			throw new \Exception('Error! '.__METHOD__.' $this->_list is not null');
			
		$data = $this->_data;
        $this->_count = count($data);
		$elem = null;
        for($i=0; $i<$this->_count; $i++)
        {	
            $this->_list[$i] = new Number($this,$data[$i],$i,$elem);
            $elem = $this->_list[$i];
        }
    }
	
	public function cloneList()
	{
		if ($this->_list===null)
			throw new \Exception('Error! '.__METHOD__.' $this->_list is null');
		
		$prevElem = null;
        for($i=0; $i<$this->_count; $i++)
        {	
            $elem = clone $this->_list[$i];
			$elem->init($this,$prevElem);
			$prevElem = $elem;
            $this->_list[$i] = $elem;
        }
	}
    
	public function setCells($value)
	{
		$this->cells = $value;
		for ($i=0; $i<$this->_count; $i++)
			$this->_list[$i]->cells = $value;
	}
	
	public function getLine(): Line
	{
		return $this->_line;
	}
	
    public function getList(): array
    {
        return $this->_list;
    }
    
    public function getCount(): int
    {
        return $this->_count;
    }

    public function getMinPos($ind):int
    {
        return $this->_list[$ind]->getPos('min');
    }

    public function getMaxPos($ind):int
    {
        return $this->_list[$ind]->getPos('max');
    }

    //для каждого числа определяем возможные границы нахождения закрашенных клеток
    public function setBounds()
    {
		for($i=0; $i<$this->_count; $i++)
			$this->_list[$i]->clearBound();
			
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->setBound();
        
        for($i=$this->_count-1; $i>=0; $i--)
            $this->_list[$i]->setBound();
    }
    
    //помечает клетки крестиками
    private function setEmptyCellsByBounds()
    {
        //перебираем все числа
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->setEmptyCellsByBound();
    }

    //закрашивает клетки для каждого числа
    private function setFullCellsByBounds()
    {
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->setFullCellsByBound();
    }
    
    public function resolve()
    {
		if ($this->_line->getUnknownCount()>0)
		{
			$this->setFullCellsByBounds();
			$this->setEmptyCellsByBounds();
		}
		else
            $this->_line->isChangeByGroups = false;
        $this->_line->isChangeByNumbers = false;
    }

    public function printBounds()
    {
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->printBound();
    }
    
    public function view()
    {
        for ($i=0; $i<$this->_count; $i++)
            echo $this->_list[$i]->length.'|';
    }
}