<?php
namespace sys;

trait TArrayAccess
{
    public function getList()
    {
        return [];
    }

    public function getMinOffset()
    {
        return 0;
    }

    public function getMaxOffset()
    {
        return null;
    }

    public function offsetExists($offset)
    {
        return isset($this->getList()[$offset]);
    }

    public function offsetGet($offset)
    {
        if ($offset<$this->getMinOffset())
            throw new \Exception('Error offset='.$offset);
        return $this->offsetExists($offset) ? $this->getList()[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->getList[] = $value;
        } else {
            /*
            if ($this->getMinOffset()!==null AND $offset<$this->getMinOffset() OR
                $this->getMaxOffset()!==null AND $this->getMaxOffset()<$offset)
                throw new \Exception($offset.' goes out of array');
            */
            $this->getList()[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->getList()[$offset]);
    }
}
