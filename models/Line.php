<?php
namespace models;

use \sys\BaseObject;

//класс для работы с одной из строк японского кроссворда
class Line extends BaseObject
{
    private $_ind;
    
    private $_numbers;
    private $_numbersCount;
    private $_cells;
    private $_cellsCount;
    private $_expectedResult;
    private $_isMirror = false;
    
    private $_maxMiniNum;
    private $_fullGroupsBasePoses;
    
    public function __construct($ind, array $numbers, string $cells, string $expectedResult=null, $isMirror = false){
        $this->_ind = $ind;
        
        $this->setNumbers($numbers);
        $this->_numbersCount = count($this->_numbers);
        
        $this->_cells = str_replace(' ','',$cells);
        $this->_cellsCount = strlen($this->_cells);
        
        $this->_maxMiniNum = $this->_cellsCount - (array_sum($this->_numbers)+$this->_numbersCount-1);
        
        if ($expectedResult!==null) {
            $this->_expectedResult = str_replace(' ', '', $expectedResult);
        }

        if ($isMirror) {
            $this->_numbers = array_reverse($this->_numbers);
            $this->_cells = strrev($this->_cells);
            if (!$this->expectedResultMustBeError()) {
                $this->_expectedResult = strrev($this->_expectedResult);
            }
            $this->_isMirror = true;
        }
    }
    
    public function setNumbers($numbers){
        $this->_numbers = [];
        foreach($numbers as $number){
            if ($number>0){
                $this->_numbers[] = $number;
            }
        }
    }
    
    public function getInd(){
        return $this->_ind;
    }

    public function getNumbers(){
        return $this->_numbers;
    }

    public function getNumbersCount(){
        return $this->_numbersCount;
    }

    public function getCells()
    {
        return $this->_cells;
    }
    
    public function getCellsCount(){
        return $this->_cellsCount;
    }

    public function expectedResultMustBeError()
    {
        return !$this->_expectedResult;
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
    
    public function solveByNoNumbers(){
        if ($this->_numbersCount!=0){
            return false;
        }
        if (strpos($this->_cells,Field::FULL_STATE)!==false){
            $this->_cells = '';
        } else {
            $this->_cells = str_repeat(Field::EMPTY_STATE, $this->_cellsCount);
        }
        return true;
    }
    
    //public function cellIsFull($pos){
    //    return $this->_cells[$pos]==Field::FULL_STATE;
    //}
    
    //public function cellIsEmpty($pos){
    //    return $this->_cells[$pos]==Field::EMPTY_STATE;
    //}
    
    //public function cellIsUnknown($pos){
    //    return $this->_cells[$pos]==Field::UNKNOWN_STATE;
    //}
    
    //public function cellIsState($pos,$state){
    //    return $this->_cells[$pos]==$state;
    //}
    
    
    
    
    public function solve():string{
        if ($this->solveByNoNumbers()){
            return $this->_cells;
        }
        $this->_fullGroupsBasePoses = $this->getFullGroupsFirstPoses();
        if (!$this->_fullGroupsBasePoses){
            return '';
        }
        $noEmptyBegPos = 0;
        for ($pos=0; $pos<$this->_fullGroupsBasePoses[0];$pos++){
            $this->_cells[$pos]=Field::EMPTY_STATE;
            $noEmptyBegPos = $pos+1;
        }
        $supposedCells = $this->getSupposedCells($this->_fullGroupsBasePoses);
        for ($i=$noEmptyBegPos; $i<$this->_cellsCount; $i++){
            if ($this->_cells[$i]!=Field::UNKNOWN_STATE){
                continue;
            }
            
            if ($supposedCells[$i] == Field::EMPTY_STATE){
                $this->_cells[$i] = Field::FULL_STATE;
                if ($this->getFullGroupsFirstPoses()){
                    $this->_cells[$i] = Field::UNKNOWN_STATE;
                } else {
                    $this->_cells[$i] = Field::EMPTY_STATE;
                }
            } elseif ($supposedCells[$i] == Field::FULL_STATE){
                $this->_cells[$i] = Field::EMPTY_STATE;
                if ($this->getFullGroupsFirstPoses()){
                    $this->_cells[$i] = Field::UNKNOWN_STATE;
                } else {
                    $this->_cells[$i] = Field::FULL_STATE;
                }
            }
        }
        return $this->_cells;
    }
    
    private function getFullGroupsFirstPoses():array{
        if ($this->_fullGroupsBasePoses===null){
            $fullGroupsFirstPoses = array_fill(0, $this->_numbersCount, 0);
        } else {
            $fullGroupsFirstPoses = $this->_fullGroupsBasePoses;
        }
        $lastNumInd = 0;
        for (;;){
            $supposedCells = $this->getSupposedCells($fullGroupsFirstPoses);

            if ($lastNumInd==0){
                $begPos = 0;
                $endPos = $this->getBegPos($fullGroupsFirstPoses,0)-1;
                for($pos=$endPos; $pos>=$begPos; $pos--){
                    if ($this->_cells[$pos]!=Field::UNKNOWN_STATE AND $this->_cells[$pos]!=$supposedCells[$pos]){
                        return [];
                    }
                }
            }
            
            $wasChange = false;
            //перебираем каждое число
            for ($numInd=0; $numInd<$this->_numbersCount; $numInd++){
                $begPos = $this->getBegPos($fullGroupsFirstPoses,$numInd);
                $endPos = $this->getEndPos($fullGroupsFirstPoses,$numInd);

                //перебираем группу клеток, соответствующие текущему числу
                //справа на лево, т.к. так быстрее работает
                for($pos=$endPos; $pos>=$begPos; $pos--){
                    //если состояние текущей клетки неизвестное
                    //или состояние текущей клетки совпадает с состоянием предполагаемой соответствующей клетки
                    if ($this->_cells[$pos]==Field::UNKNOWN_STATE OR $this->_cells[$pos]==$supposedCells[$pos]){
                        //переходим в следующую клетку
                        continue;
                    }
                    if ($this->_cells[$pos]==Field::FULL_STATE){
                        //текущую группу из заштрихованных клеток двигаем вправо так, 
                        //чтобы правый конец оказался в позиции $pos
                        $fullGroupsFirstPoses[$numInd]+=$pos-$this->getEndFullPos($begPos,$numInd);
                    } elseif ($this->_cells[$pos]==Field::EMPTY_STATE) {
                        //текущую группу из заштрихованных клеток двигаем вправо так, 
                        //чтобы левый конец оказался в позиции $pos+1
                        $fullGroupsFirstPoses[$numInd]+=$pos-$begPos+1;
                    }
                    //если текущая группа из заштрихованных клеток
                    //зашла за границу клеток
                    if ($fullGroupsFirstPoses[$numInd]>$this->_maxMiniNum){
                        //считаем, что исходные данные ошибочные
                        return [];
                    }

                    for ($j=$numInd+1; $j<$this->_numbersCount; $j++){
                        if ($fullGroupsFirstPoses[$numInd]<=$fullGroupsFirstPoses[$j]){
                            break;
                        } else {
                            $fullGroupsFirstPoses[$j] = $fullGroupsFirstPoses[$numInd];
                        }
                    }
                    $wasChange = true;
                    $lastNumInd = $numInd;
                    break(2);
                    
                }
            }
            
            if(!$wasChange){
                return $fullGroupsFirstPoses;
            }
        }
    }
    
    private function getBegPos($fullGroupsFirstPoses,$numInd){
        $pos=0;
        for($i=0; $i<$numInd; $i++){
            $pos+=$this->_numbers[$i]+1;
        }
        return $pos+$fullGroupsFirstPoses[$numInd];
    }
    
    private function getEndPos($fullGroupsFirstPoses,$numInd){
        $miniPosInd = $numInd+1;
        if ($miniPosInd==$this->_numbersCount){
            return $this->_cellsCount-1;
        }
        return $this->getBegPos($fullGroupsFirstPoses,$miniPosInd)-1;
    }
    
    private function getEndFullPos($begPos,$numInd){
        return $begPos+$this->_numbers[$numInd]-1;
    }

    private function getSupposedCells($fullGroupsFirstPoses){
        $supposedCells='';
        for ($numInd=0; $numInd<$this->_numbersCount; $numInd++){
            if ($numInd!=0){
                $supposedCells.=Field::EMPTY_STATE;
            }
            
            if ($numInd!=0){
                $l = $fullGroupsFirstPoses[$numInd] - $fullGroupsFirstPoses[$numInd-1];
            } else {
                $l = $fullGroupsFirstPoses[$numInd];
            }
            
            $supposedCells.= str_repeat(Field::EMPTY_STATE, $l)
                    .str_repeat(Field::FULL_STATE, $this->_numbers[$numInd]);
        }
        $cellsCount = strlen($supposedCells);
        for ($i=$cellsCount; $i<$this->_cellsCount; $i++){
            $supposedCells.=Field::EMPTY_STATE;
        }
        return $supposedCells;
    }

    private function nextMiniCell($nums){
        $i = $this->_numbersCount-1;
        $oldNums = $nums;
        while($i>=0){
            if ($nums[$i]<$this->_maxMiniNum){
                $nums[$i]++;
                return $nums;
            } else {
                $nums[$i]=0;
                if ($i!=0 AND $nums[$i-1]<$this->_maxMiniNum){
                    $nums[$i-1]++;
                    for ($j=$i; $j<$this->_numbersCount; $j++){
                        $nums[$j] = max($nums[$i-1],$nums[$j]);
                    }
                    return $nums;
                }
                if ($i==0){
                    return $oldNums;
                }
            }
            $i--;
        }
    }
}