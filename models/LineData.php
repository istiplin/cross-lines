<?php
namespace models;

class LineData {
    private $_numbers;
    private $_cells;
    private $_expectedResult;
    
    private $_isMirror = false;
    
    private $_errorMessage;
    private $_result;
    
    public function __construct(array $numbers, string $cells, string $expectedResult=null, $isMirror=false)
    {
        $this->_numbers = $numbers;
        $this->_cells = str_replace(' ','',$cells);
        if ($expectedResult) {
            $this->_expectedResult = str_replace(' ', '', $expectedResult);
        }

        if ($isMirror) {
            $this->mirror();
        }
    }

    private function mirror()
    {
        $this->_numbers = array_reverse($this->_numbers);
        $this->_cells = strrev($this->_cells);
        if (!$this->expectedResultMustBeError()) {
            $this->_expectedResult = strrev($this->_expectedResult);
        }

        $this->_isMirror = !$this->_isMirror;
    }
    
    public function expectedResultMustBeError()
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
            if (strlen($view)) {
                $view .= '|';
            }
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
        if ($this->_isMirror) {
            return 'MIRROR ';
        }
        return '';
    }
    
    public function getErrorMessage():string{
        return $this->_errorMessage;
    }

    public function setError($message)
    {
        if ($this->expectedResultMustBeError()){
            $this->_errorMessage = '';
        } else {
            $this->_errorMessage = 'error message: ' . $message;
        }
        $this->_result = 'error';
    }
    
    public function setResult($result)
    {
        $this->_errorMessage = '';
        $this->_result = $result;
    }
    
    public function isSetError():bool{
        return isset($this->_errorMessage);
    }
    
    public function getIsError():bool{
        return !empty($this->_errorMessage);
    }
    
    public function getResult(): string{
        return $this->_result;
    }
}
