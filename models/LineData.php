<?php
namespace models;

class LineData {
    public $ind;
    private $_numbers;
    private $_cells;
    private $_expectedResult;
    
    private $_isMirror = false;
    
    public $isError;
    private $_errorMessage;
    public $result;
    private $_changeIds;
    
    public function __construct($ind, array $numbers, string $cells, string $expectedResult=null, $isMirror=false)
    {
        $this->ind = $ind;
        $this->_numbers = $numbers;
        $this->_cells = str_replace(' ','',$cells);
        if ($expectedResult)
            $this->_expectedResult = str_replace(' ','',$expectedResult);
        
        if ($isMirror)
            $this->mirror();
    }
    
    public function getInputCells()
    {
        return $this->_cells;
    }
    
    private function mirror()
    {
        $this->_numbers = array_reverse($this->_numbers);
        $this->_cells = strrev($this->_cells);
        if ($this->_expectedResult!=='error')
            $this->_expectedResult = strrev($this->_expectedResult);
        
        $this->_isMirror = !$this->_isMirror;
    }
    
    public function expectedresultIsError()
    {
        return $this->_expectedResult==='error';
    }
    
    public function getNumbersList()
    {
        return $this->_numbers;
    }
    
    public function getNumbersView()
    {
	$view = '';
        foreach ($this->_numbers as $length)
        {
            if (strlen($view))
                $view.='|';
            $view.=$length;
        }
	return $view;
    }
    
    public function getCellsStr()
    {
        return $this->_cells;
    }
    
    public function getExpectedResult()
    {
        return $this->_expectedResult;
    }
    
    public function getMirrorStatus()
    {
        if ($this->_isMirror)
            return 'MIRROR ';
        return '';
    }
    
    public function setError($message)
    {
        $this->isError = true;
        $this->_errorMessage = $message;
        $this->result = 'error';
    }
    
    public function setSuccess($result)
    {
        $this->isError = false;
        $this->result = $result;
    }
    
    public function getChangeIds($fieldHeight=null,$solveLinesIds=[])
    {
        if ($this->_changeIds!==null)
            return $this->_changeIds;
        
        if ($this->isError===null)
            throw new \Exception('this->isError is null');
        
        if ($this->isError)
            throw new \Exception('this->isError is true');
        
        $this->_changeIds = $solveLinesIds;
        
        if (count($solveLinesIds))
            unset($solveLinesIds[$this->ind]);
        
        $this->_changeIds = $solveLinesIds;
        
        $offset = 0;
        if ($fieldHeight!==null AND $this->ind < $fieldHeight) {
            $offset = $fieldHeight;
        }
        
        $count = strlen($this->_cells);
        for ($i=0; $i<$count; $i++)
        {
            if ($this->_cells[$i]!==$this->result[$i])
            {
                $ind = $i+$offset;
                $this->_changeIds[$ind] = $ind;
            }
        }
        return $this->_changeIds;
    }
    
    public function getErrorMessage()
    {
        if ($this->result === 'error' AND !$this->expectedResultIsError())
            return 'error message: '.$this->_errorMessage;
        return '';
    }
}
