<?php
namespace models;

use \sys\BaseObject;

//����� ��� ������ � ����� ��������� ����������, ��� ��� ������������� �� ��������� ����������,
//�.�. ������ ������ ������������� ������� ����� ���������� - � �������������� ������ �����, 
//����� ������ - � ������� ������. 
//�������� �������, ��� SlowField
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
				//������� ������� ������ �� ������, �.�. ��� ������� ��������� ��� �� ���� � ��������������
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