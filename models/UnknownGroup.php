<?php
namespace models;

//класс для работы с группой непомеченных соседних клеток
class UnknownGroup extends EmptyGroup
{
    protected $_state = Cell::UNKNOWN_STATE;
    private $_hasEmpty;

    public function isUnknown():bool
    {
        return true;
    }

    public function isFull():bool
    {
        return false;
    }

    public function isEmpty():bool
    {
        return false;
    }

    public function getHasEmpty():bool
    {
        //return false;
        if ($this->_hasEmpty!==null)
            return $this->_hasEmpty;
        
        $length = $this->length;

        $prevMax = 0;
        $nextMax = 0;
        if ($this->prevIsFull())
        {
            $length += $this->prev->length;
            $prevMax = $this->prev->getGroupNumbersMaxLength();
        }
        else
             //считаем, что текущая группа может быть полностью закрашена, т.е. крестика тут может не быть
            return $this->_hasEmpty = false;
        
        if ($this->nextIsFull())
        {
            $length += $this->next->length;
            $nextMax = $this->next->getGroupNumbersMaxLength();
        }
        else
            //считаем, что текущая группа может быть полностью закрашена, т.е. крестика тут может не быть
            return $this->_hasEmpty = false;
        
        $min = min($prevMax,$nextMax);
        
        //если минимальная из максимальных длин закрашенной группы не превышает полученную длину из текущей группы и его соседних
        if ($min<$length)
            //считаем, что текущая группа не может быть полностью закрашена, т.е. крестик в текущей группе точно есть
            return $this->_hasEmpty = true;
        //иначе считаем, что текущая группа может быть полностью закрашена, т.е. крестика тут может не быть
        return $this->_hasEmpty = false;
    }
}