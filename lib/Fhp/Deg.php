<?php

namespace Fhp;

class Deg
{
    /** @var array */
    protected $dataElements = [];

    /**
     * Adds a data element to the data element group.
     *
     * @param mixed $value
     */
    public function addDataElement($value)
    {
        $this->dataElements[] = $value;
    }

    public function toString()
    {
        return (string) implode(':', $this->dataElements);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
