<?php
namespace models;

use \sys\BaseObject;

/**
 * Класс для работы со всеми клетками поля класса Field
 */
class Field extends BaseObject{
    const UNKNOWN_STATE = '0';
    const FULL_STATE = '1';
    const EMPTY_STATE = '2';
    
    public $name;
    
    public $maxDuration;
    public $duration;
    public $beginTime;
    
    private $_width;
    private $_height;
    private $_count = 0;
    
    private $_cells = [];
    private $_numsList;
    
    private $_unknownCells = [];
    private $_solveLinesIds = [];
    
    private $_cellsTrialLevels = [];
    private $_currentTrialLevel = -1;
    
    private $_testCells = [];
    private $_isTest = false;
    private $_currTestCellId;   //номер клетки, которая рассматривается при методе от противного
    
    public function __construct($horNums, $vertNums, $cellsStrArr=null, $name=null)
    {
        $this->name = $name;
        
        $this->_width = count($vertNums);
        $this->_height = count($horNums);
        
        $this->_numsList = array_merge($horNums,$vertNums);

        $this->setCellsState($cellsStrArr);
        
        $this->_count = $this->_width * $this->_height;
    }
    
    public function sizeView()
    {
        return $this->_width.'X'.$this->_height;
    }

    public function getHorNums()
    {
        return array_slice($this->_numsList,0,$this->_height);
    }

    public function getVertNums()
    {
        return array_slice($this->_numsList,$this->_height,$this->_width);
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
    
    public function timeIsUp(): bool
    {
        $this->duration = microtime(true) - $this->beginTime;
        if ($this->maxDuration)
            return $this->duration>=$this->maxDuration;
        return false;
    }
   
    public function solve(): bool{
        $this->beginTime = microtime(true);

        while(count($this->_solveLinesIds)){
            if (!$this->solveLines()) {
                return false;
            }

            if (count($this->_unknownCells)) {
                $this->solveByTrial();
            }
        }
        
        //$this->timeIsUp();
        return true;

        
        /*
        if ($this->solveLines()){
            $this->_currentTrialLevel++;
            $cellId = key($this->_unknownCells);

            //echo 'beg-'.$cellId.'<br>';

            $this->setCellState($cellId, Field::FULL_STATE);
            $this->addSolveLinesIdsByCellId($cellId);
        } else {
            return false;
        }
        
        $res = $this->solveLines();
        
            $cellsTrialLevels = array_reverse($this->_cellsTrialLevels,true);
            $lastCellId = null;
            foreach ($cellsTrialLevels as $cellId=>$trialLevel){
                if ($trialLevel==$this->_currentTrialLevel){
                    $lastCellId = $cellId;
                    $this->setCellState($cellId, Field::UNKNOWN_STATE);
                    unset($this->_cellsTrialLevels[$cellId]);
                } else {
                    break;
                }
            }
            $this->_currentTrialLevel--;

            if (!$res){
                $this->setCellState($lastCellId, Field::EMPTY_STATE);
                $this->addSolveLinesIdsByCellId($lastCellId);
            }
        
        
        
        //$this->timeIsUp();
        return true;
         * 
         */
    }

    //разгадывает кроссворд методом проб и ошибок
    private function solveByTrial(){
        //$sort = $this->getSortUnknownCells();

        foreach($this->_unknownCells as $id=>$val)
        {
            if ($this->timeIsUp())
            {
                $this->_currTestCellId = $id;
                return;
            }

            if ($this->trySolveLinesByTrial($id,self::FULL_STATE,self::EMPTY_STATE))
                return;

        }

    }

    private function trySolveLinesByTrial($tryCellId,$firstState,$secondState):bool{
        $this->onTest();
        $this->setCellState($tryCellId, $firstState);
        $this->addSolveLinesIdsByCellId($tryCellId);
        
        //если разгадывание строки завершилось ошибкой
        if (!$this->solveLines())
        {
            //то клетка $tryCellId не имеет состояние $firstState и,
            //следовательно, имеет состояние $secondState
            $this->offTest();
            $this->setCellState($tryCellId, $secondState);
            $this->addSolveLinesIdsByCellId($tryCellId);

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

    private function solveLines():bool{
        $isChange = false;
        while(count($this->_solveLinesIds)){
            $solveLinesIds = $this->_solveLinesIds;
            $this->_solveLinesIds = [];
            foreach ($solveLinesIds as $lineId){
                $line = $this->getLine($lineId);
                if (!$line->solve()){
                    return false;
                }
                $this->setLine($line);
                
                if (!$isChange AND count($this->_solveLinesIds)){
                    $isChange = true;
                }
                
                if ($isChange AND $this->timeIsUp()){
                    return !$this->_isTest;
                }
            }
        }
        return true;
    }
    
    private function setLine(Line $line){
        $lineId = $line->getInd();
        $lineCells = $line->getCells();
        
        if (!empty($this->_solveLinesIds)){
            unset($this->_solveLinesIds[$lineId]);
        }
        
        $strlen = strlen($lineCells);
        
        //если в поле меняем горизонтальную строку
        if ($lineId < $this->_height) {
            $currCellId = $this->getCellId(0,$lineId);
            for ($i = 0; $i < $strlen; $i++) {
                if ($this->getCellState($currCellId+$i)!==$lineCells[$i]){
                    $this->setCellState($currCellId+$i,$lineCells[$i]);
                    
                    $solveLineId = $this->_height+$i;
                    $this->_solveLinesIds[$solveLineId] = $solveLineId;
                }
            }
        //если в поле меняем вертрикальную строку
        }else{
            $currCellId = $this->getCellId($lineId - $this->_height,0);
            for ($i = 0; $i < $strlen; $i++) {
                if ($this->getCellState($currCellId)!==$lineCells[$i]){
                    $this->setCellState($currCellId,$lineCells[$i]);
                    $this->_solveLinesIds[$i] = $i;
                }
                $currCellId+=$this->_width;
            }
        }
    }

    private function setCellsState($cellsStrArr=null){
        $id=0;
        for ($y = 0; $y < $this->_height; $y++) {
            for ($x = 0; $x < $this->_width; $x++) {
                if ($cellsStrArr) {
                    $cellsStrArr[$y] = str_replace(' ','',$cellsStrArr[$y]);
                    $state = $cellsStrArr[$y][$x];
                } else {
                    $state = self::UNKNOWN_STATE;
                }

                $this->setCellState($id,$state);
                if ($state == self::UNKNOWN_STATE){
                    $this->addSolveLinesIdsByCellId($id);
                }
                $id++;
            }
        }
    }

    private function setCellState($id,$state){
        if ($state===self::UNKNOWN_STATE){
            unset($this->_cells[$id]);
            if ($this->_isTest) {
                $this->_unknownCells[$id] = $this->_testCells[$id];
                unset($this->_testCells[$id]);
            } else {
                $this->_unknownCells[$id] = 0;
            }

        } else {
            $this->_cells[$id] = $state;
            if ($this->_isTest){
                $this->_testCells[$id] = $this->_unknownCells[$id];
            }
            unset($this->_unknownCells[$id]);
            
            if ($this->_currentTrialLevel!=-1){
                $this->_cellsTrialLevels[$id] = $this->_currentTrialLevel;
            }
        }
    }
    
    private function getCellState($id){
        if (array_key_exists($id, $this->_cells)) {
            return $this->_cells[$id];
        } else {
            return self::UNKNOWN_STATE;
        }
    }

    private function getLine($lineId): Line{
        $cells = $this->getLineCells($lineId);
        return new Line($lineId,$this->_numsList[$lineId],$cells);
    }
    
    private function addSolveLinesIdsByCellId($cellId){
        $xLineId = ($cellId%$this->_width)+$this->_height;
        $this->_solveLinesIds[$xLineId]=$xLineId;

        $yLineId = (int)($cellId/$this->_width);
        $this->_solveLinesIds[$yLineId]=$yLineId;
    }

    private function onTest(){
        $this->_isTest = true;
    }
    
    private function offTest(){
        foreach($this->_testCells as $id=>$count){
            $this->setCellState($id, self::UNKNOWN_STATE);
        }
        $this->_solveLinesIds = [];
        $this->_isTest = false;
    }

    private function getLineCells($lineId){
        $lineCells = '';
        if ($lineId < $this->_height) {
            $begCellId = $this->getCellId(0,$lineId);
            $endCellId = $begCellId + $this->_width;
            for ($id = $begCellId; $id < $endCellId; $id++){
                $lineCells .= $this->getCellState($id);
            }
        } else {
            $begCellId = $this->getCellId($lineId - $this->_height,0);
            for ($id = $begCellId; $id < $this->_count ;$id+=$this->_width){
                $lineCells .= $this->getCellState($id);
            }
        }
        return $lineCells;
    }
    
    private function leftIsEmpty($id)
    {
        if ($id%$this->_width==0)
            return true;
        
        $leftId = $id-1;
        if (isset($this->_cells[$leftId]) AND $this->_cells[$leftId]==self::EMPTY_STATE){
            return true;
        }
        
        return false;
    }
    
    private function rightIsEmpty($id)
    {
        if ($id%$this->_width==$this->_width-1){
            return true;
        }
        
        $rightId = $id+1;
        if (isset($this->_cells[$rightId]) AND $this->_cells[$rightId]==self::EMPTY_STATE){
            return true;
        }
         
        return false;
    }
    
    private function upIsEmpty($id)
    {
        $upId = $id-$this->_width;
        if ($upId<0){
            return true;
        }
        
        if (isset($this->_cells[$upId]) AND $this->_cells[$upId]==self::EMPTY_STATE){
            return true;
        }
        
        return false;
    }
    
    private function downIsEmpty($id)
    {
        $downId = $id+$this->_width;
        if ($downId>$this->_count)
            return true;
        
        
        if (isset($this->_cells[$downId]) AND $this->_cells[$downId]==self::EMPTY_STATE){
            return true;
        }
        
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

}
