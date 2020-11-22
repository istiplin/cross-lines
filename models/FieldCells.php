<?php

namespace models;

/**
 * Класс для работы со всеми клетками поля класса Field
 */
class FieldCells {

    public $name;
    
    private $_width;
    private $_height;
    private $_count = 0;
    
    private $_cells = [];
    private $_testCells = [];
    private $_isTest = false;
    private $_currTestCellId;   //номер клетки, которая рассматривается при методе от противного
    
    private $_unknownCells = [];
    
    private $_numsList;
    
    private $_solveLinesIds = [];
    
    public $maxDuration;
    public $duration;
    public $beginTime;
    
    public function __construct($horNums, $vertNums, $cellsStrArr=null, $name=null)
    {
        $this->name = $name;
        
        $this->_width = count($vertNums);
        $this->_height = count($horNums);
        
        $this->_numsList = array_merge($horNums,$vertNums);

        $this->setCellsState($cellsStrArr);
        
        $this->_count = $this->_width * $this->_height;
    }
    
    private function setCellsState($cellsStrArr=null)
    {
        $id=0;
        for ($y = 0; $y < $this->_height; $y++) {
            if ($cellsStrArr) {
                $cellsStrArr[$y] = str_replace(' ','',$cellsStrArr[$y]);
                for ($x = 0; $x < $this->_width; $x++) {
                    $this->setCellState($id++,$cellsStrArr[$y][$x]);
                    if ($cellsStrArr[$y][$x] == Cell::UNKNOWN_STATE){
                        $this->_solveLinesIds[$y] = $y;
                        
                        $ind = $x+$this->_height;
                        $this->_solveLinesIds[$ind] = $ind;
                    }
                }
            }
            else{
                for ($x = 0; $x < $this->_width; $x++) {
                    $this->setCellState($id++,Cell::UNKNOWN_STATE);
                    $this->_solveLinesIds[$y] = $y;
                    
                    $ind = $x+$this->_height;
                    $this->_solveLinesIds[$ind] = $ind;
                }
            }
        }
    }
    
    private function setCellState($id,$state, $isChangeSolveLinesIds = false)
    {
        if ($state===Cell::UNKNOWN_STATE){
            unset($this->_cells[$id]);
            if ($this->_isTest) {
                $this->_unknownCells[$id] = $this->_testCells[$id];
                unset($this->_testCells[$id]);
            } else {
                $this->_unknownCells[$id] = 0;
            }
        }
        else{
            $this->_cells[$id] = $state;
            if ($this->_isTest){
                $this->_testCells[$id] = $this->_unknownCells[$id];
            }
            unset($this->_unknownCells[$id]);

        }
        
        if ($isChangeSolveLinesIds)
        {
            $xLineId = ($id%$this->_width)+$this->_height;
            $this->_solveLinesIds[$xLineId]=$xLineId;
            
            $yLineId = (int)($id/$this->_width);
            $this->_solveLinesIds[$yLineId]=$yLineId;
        }
    }
    
    private function getCellState($id)
    {
        if (array_key_exists($id, $this->_cells)) {
            return $this->_cells[$id];
        } else {
            return Cell::UNKNOWN_STATE;
        }
    }
    
    public function timeIsUp(): bool
    {
        $this->duration = microtime(true) - $this->beginTime;
        if ($this->maxDuration)
            return $this->duration>=$this->maxDuration;
        return false;
    }
    
    protected function solveLines():bool
    {
        $isChange = false;
        while(count($this->_solveLinesIds))
        {
            $solveLinesIds = $this->_solveLinesIds;
            $this->_solveLinesIds = [];
            foreach ($solveLinesIds as $lineId)
            {   
                $lineData = $this->getLineOutput($lineId);
                if ($lineData->isError){
                    return false;
                }
                $this->_solveLinesIds = $lineData->getChangeIds($this->_height,$this->_solveLinesIds);
                if (!$isChange AND count($this->_solveLinesIds)){
                    $isChange = true;
                }
                
                if ($isChange AND $this->timeIsUp()){
                    return !$this->isTest;
                }
            }
        }
        return true;
    }
    
    private function onTest()
    {
        $this->_isTest = true;
    }
    
    private function offTest()
    {
        foreach($this->_testCells as $id=>$count){
            $this->setCellState($id, Cell::UNKNOWN_STATE);
        }
        $this->_solveLinesIds = [];
        $this->_isTest = false;
    }
    
    private function trySolveLines($tryCellId,$firstState,$secondState):bool
    {
        $this->onTest();
        $this->setCellState($tryCellId, $firstState, true);

        if (!$this->solveLines())
        {
            $this->offTest();
            $this->setCellState($tryCellId, $secondState, true);

            return true;
        }
        else
        {
            $this->_testCells[$tryCellId] = count($this->_testCells);
            $this->offTest();
            
            if (count($this->_unknownCells)==0){
                return true;
            }
        }
        return false;
    }
	
    //разгадывает кроссворд методом проб и ошибок
    private function trySolve()
    {
        //$sort = $this->getSortUnknownCells();

        foreach($this->_unknownCells as $id=>$val)
        {
            if ($this->timeIsUp())
            {
                $this->_currTestCellId = $id;
                return;
            }

            if ($this->trySolveLines($id,Cell::FULL_STATE,Cell::EMPTY_STATE))
                return;

        }

    }
    
    public function solve(): bool
    {
        $this->beginTime = microtime(true);

        while(count($this->_solveLinesIds)){
            if (!$this->solveLines())
                return false;

            $this->trySolve();
        }

        //$this->timeIsUp();
        return true;
    }
    
    private function leftIsEmpty($id)
    {
        if ($id%$this->_width==0)
            return true;
        
        $leftId = $id-1;
        if (array_key_exists($leftId, $this->_cells) AND $this->_cells[$leftId]==Cell::EMPTY_STATE)
            return true;
        
        return false;
    }
    
    private function rightIsEmpty($id)
    {
        if ($id%$this->_width==$this->_width-1)
            return true;
        
        $rightId = $id+1;
        if (array_key_exists($rightId, $this->_cells) AND $this->_cells[$rightId]==Cell::EMPTY_STATE)
            return true;
         
        return false;
    }
    
    private function upIsEmpty($id)
    {
        $upId = $id-$this->_width;
        if ($upId<0)
            return true;
        
        if (array_key_exists($upId, $this->_cells) AND $this->_cells[$upId]==Cell::EMPTY_STATE)
            return true;
        
        return false;
    }
    
    private function downIsEmpty($id)
    {
        $downId = $id+$this->_width;
        if ($downId>$this->_count)
            return true;
        
        
        if (array_key_exists($downId, $this->_cells) AND $this->_cells[$downId]==Cell::EMPTY_STATE)
            return true;
        
        return false;
    }
    
    private function getSortUnknownCells()
    {

        foreach($this->_unknownCells as $id=>$count)
        {
            if ($this->leftIsEmpty($id) AND $this->upIsEmpty($id)
                OR $this->leftIsEmpty($id) AND $this->downIsEmpty($id)
                OR $this->rightIsEmpty($id) AND $this->upIsEmpty($id) 
                OR $this->rightIsEmpty($id) AND $this->downIsEmpty($id)){
                    $sort[0][]=$id;
            }
            elseif($this->leftIsEmpty($id) 
                OR $this->rightIsEmpty($id)
                OR $this->upIsEmpty($id) 
                OR $this->downIsEmpty($id)){
                    $sort[1][]=$id;
            }
            else
                $sort[2][]=$id;
        }
        return $sort;
    }
    
    private function getCellId($x,$y)
    {
        return $y*$this->_width + $x;
    }
    /*
    private function getCellCoords($id)
    {
        return [$id%$this->_width,(int)($id/$this->_width)];
    }
     * 
     */
    
    private function getLineCells($lineId)
    {
        $lineCells = '';
        if ($lineId < $this->_height) {
            $begCellId = $this->getCellId(0,$lineId);
            $endCellId = $begCellId + $this->_width;
            for ($id = $begCellId; $id < $endCellId; $id++){
                $lineCells .= $this->getCellState($id);
            }
        }
        else
        {
            $begCellId = $this->getCellId($lineId - $this->_height,0);
            for ($id = $begCellId; $id < $this->_count ;$id+=$this->_width){
                $lineCells .= $this->getCellState($id);
            }
        }
        return $lineCells;
    }
    
    private function setLineCells($lineId,$lineCells)
    {
        $strlen = strlen($lineCells);
        
        if ($lineId < $this->_height) {
            $currCellId = $this->getCellId(0,$lineId);
            
            for ($i = 0; $i < $strlen; $i++) {
                if ($this->getCellState($currCellId+$i)!==$lineCells[$i]){
                    $this->setCellState($currCellId+$i,$lineCells[$i]);
                }
            }
        }else{
            $currCellId = $this->getCellId($lineId - $this->_height,0);
            
            for ($i = 0; $i < $strlen; $i++) {
                if ($this->getCellState($currCellId)!==$lineCells[$i]){
                    $this->setCellState($currCellId,$lineCells[$i]);
                }
                $currCellId+=$this->_width;
            }
        }
        
    }
    
    public function getLineOutput($lineId)
    {
        $cellsStr = $this->getLineCells($lineId);
        $line = new Line($lineId,$this->_numsList[$lineId],$cellsStr);
        $lineData = $line->getOutput();
        if ($lineData->isError){
            return $lineData;
        }
        $this->setLineCells($lineId,$lineData->result);

        return $lineData;
    }
    
    public function getCells()
    {
        $cells=[];
        $i=0;
        for ($y = 0; $y < $this->_height; $y++) {
            for ($x = 0; $x < $this->_width; $x++) {
                $cells[$y][$x] = $this->getCellState($i);
                $i++;
            }
        }
        return $cells;
        
    }
    
    public function getHorNums()
    {
        return array_slice($this->_numsList,0,$this->_height);
    }

    public function getVertNums()
    {
        return array_slice($this->_numsList,$this->_height,$this->_width);
    }
}
