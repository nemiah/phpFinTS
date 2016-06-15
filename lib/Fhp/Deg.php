<?php

namespace Fhp;

/**
 * Class Deg (Data Element Group)
 * @package Fhp
 */
class Deg
{
    /** @var array  */
    protected $dataElements = array();

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
