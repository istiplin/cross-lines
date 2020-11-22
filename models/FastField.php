<?php
namespace models;

use \sys\BaseObject;

//класс для работы с полем японского кроссворда, где оно разгадывается по отдельным алгоритмам,
//т.е. каждая строка разгадывается сначала одним алгоритмом - с использованием только чисел, 
//потом другим - с помощью блоков. 
//Работает быстрей, чем SlowField
class FastField extends Field{

    private $_solveLinesByNumbers = [];
    private $_solveLinesByGroups = [];

    public function addSolveLineByNumbers($line)
    {
        $this->_solveLinesByNumbers[$line->id] = $line;
    }

    public function addSolveLine($line)
    {
        $this->_solveLinesByNumbers[$line->id] = $line;
        $this->_solveLinesByGroups[$line->id] = $line;
    }
	
    private function solveLinesByNumbers(): bool
    {
        while(count($this->_solveLinesByNumbers))
        {
            if ($this->timeIsUp())
                return !$this->isTest;

            $this->_isChange = true;
            $lines = $this->_solveLinesByNumbers;
            $this->_solveLinesByNumbers = [];
            foreach($lines as $line)
            {
                if ($this->timeIsUp())
                    return !$this->isTest;

                if (!$line->solveByNumbers())
                {
                    $this->_solveLinesByNumbers = [];
                    $this->_solveLinesByGroups = [];
                    return false;
                }
                //удаляем текущую строку из списка, т.к. для данного алгоритма нам не нало её пересматривать
                unset($this->_solveLinesByNumbers[$line->id]);
            }

            //if ($this->timeIsUp())
            //	return true;
        }
        return true;
    }

    private function solveLinesByGroups(): bool
    {
        while(count($this->_solveLinesByGroups))
        {
            if ($this->timeIsUp())
                return !$this->isTest;

            $this->_isChange = true;
            $lines = $this->_solveLinesByGroups;
            $this->_solveLinesByGroups = [];

            foreach($lines as $line)
            {	
                if ($this->timeIsUp())
                    return !$this->isTest;

                if (!$line->solveByGroups())
                {
                    $this->_solveLinesByNumbers = [];
                    $this->_solveLinesByGroups = [];
                    return false;
                }
            }

            //if ($this->timeIsUp())
            //	return true;
        }
        return true;
    }
	
    protected function solveLines(): bool
    {
        while ($this->_isChange) {

            $this->_isChange = false;

            if (!$this->solveLinesByNumbers())
                return false;

            if (!$this->solveLinesByGroups())
                return false;

        }
        return true;
    }

}