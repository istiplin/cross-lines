<?php
namespace models;

use \sys\BaseObject;
use \sys\TArrayAccess;

//класс для работы с числами в строке, по которым строится рисунок
class Numbers extends BaseObject implements \ArrayAccess
{
    use TArrayAccess;
    
    private $_line;
	private $_cells;
	private $_field;
	private $_groups;
	
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
		$this->_field = $value->getField();
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
    
	public function setCells($value)
	{
		$this->_cells = $value;
		for ($i=0; $i<$this->_count; $i++)
			$this->_list[$i]->cells = $value;
	}
	
	public function setGroups($value)
	{
		$this->_groups = $value;
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
	
	public function getMinLength($begInd=null, $endInd=null)
	{
		$begInd = ($begInd===null)?0:$begInd;
		$endInd = ($endInd===null)?$this->_count-1:$endInd;
		$minLength = $this->_cells->getCount();
		for($i=$begInd; $i<=$endInd; $i++)
			$minLength = min($minLength,$this->_list[$i]->getLength());
		return $minLength;
	}
	
	public function getMaxLength($begInd=null, $endInd=null)
	{
		$begInd = ($begInd===null)?0:$begInd;
		$endInd = ($endInd===null)?$this->_count-1:$endInd;
		$maxLength = 0;
		for($i=$begInd; $i<=$endInd; $i++)
			$maxLength = max($maxLength,$this->_list[$i]->getLength());
		return $maxLength;
	}

    public function getMinPos($ind):int
    {
        return $this->_list[$ind]->getPos('min');
    }

    public function getMaxPos($ind):int
    {
        return $this->_list[$ind]->getPos('max');
    }

	public function clearBounds()
	{
		for($i=0; $i<$this->_count; $i++)
			$this->_list[$i]->clearBound();
	}
	
    //для каждого числа определяем возможные границы нахождения закрашенных клеток
    public function setBounds($isDetail=false)
    {
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->setBound();
        
        for($i=$this->_count-1; $i>=0; $i--)
            $this->_list[$i]->setBound();
			
		if ($isDetail)
			$this->viewBounds();
    }
    
    //помечает клетки крестиками
    public function setEmptyCells()
    {
        //перебираем все числа
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->setEmptyCells();
    }

    //закрашивает клетки для каждого числа
    public function setFullCells()
    {
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->setFullCells();
    }
    
	public function setStateCells($isView=false)
	{
		$this->setFullCells();
		$this->setEmptyCells();
		
		if ($isView)
			echo $this->_line->getView($isView).' numbers-<b>RESULT</b><br>';
	}
	
    public function solve($isView=false, $isDetail=false)
    {		
		if ($this->_line->getUnknownCount()>0)
		{
		
			//переопределяем список блоков в строке
			$this->_groups->resetList();

			$this->clearBounds();
			
			//определяем границы для каждого числа в строке
			$this->setBounds();
			
			$this->_groups->setGroupNumbers();
			
			if ($isDetail)
				$this->viewBounds();
		
			$this->setFullCells();
			$this->setEmptyCells();
			
			if ($isView)
				echo $this->_line->getView(true).' num-<b>RESULT</b><br>';
		}
    }

    public function viewBounds()
    {
        for($i=0; $i<$this->_count; $i++)
            $this->_list[$i]->printBound();
    }
	
	public function view()
	{
		$this->viewBounds();
		echo $this->_line->getView().' num-<b>RESULT</b><br>';
	}
    
    public function getLengthView(array $boldKeys=null)
    {
		$view = '';
        for ($i=0; $i<$this->_count; $i++)
		{
			if ($boldKeys AND in_array($i,$boldKeys))
				$view.='<b>'.$this->_list[$i]->length.'</b>|';
			else
				$view.=$this->_list[$i]->length.'|';
		}
		return $view;
    }
	
	public function getLengthArray()
	{
		$lengthArr = [];
		for($i=0; $i<$this->_count; $i++)
			$lengthArr[$i] = $this->_list[$i]->getLength();
			
		return $lengthArr;
	}
}