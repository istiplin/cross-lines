<?php
namespace models;

use \sys\BaseObject;

//����� ��� ������ � ����� ��������� ����������, ��� ��� ������������� �� ������������ �� 2-� ����������,
//�.�. ������ ������ ������������� ����� 2-�� �������� � �������������� ����� � ������.
//�������� ��������� ��� FastField, �� ���� ���������� �����, ������� ������������ � ����-������
class SlowField extends Field{

	private $_solveLines = [];
	
	public function addSolveLineByNumbers($line)
	{
		$this->_solveLines[$line->id] = $line;
	}
	
	public function addSolveLine($line)
	{
		$this->_solveLines[$line->id] = $line;
	}
	
	public function delSolveLine($line)
	{
		unset($this->_solveLines[$line->id]);
	}
	
	protected function solveLines():bool
	{
		$this->_isChange = false;
		while (count($this->_solveLines)) 
		{
			$lines = $this->_solveLines;
			$this->_solveLines = [];
			
			foreach($lines as $line)
			{	
                if (!$line->trySolveTest())
				{
					//$this->_solveLines = [];
                    return false;
				}
			}
			
		}
		return true;
	}
}