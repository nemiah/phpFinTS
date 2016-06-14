<?php

namespace Fhp;

/**
 * Class Deg (Data Element Group)
 * @package Fhp
 */
class Deg
{
    protected $dataElements = array();

    public function addDataElement($value)
    {
        $this->dataElements[] = $value;
    }

    public function toString()
    {
        return (string) implode(':', $this->dataElements);
    }

    public function __toString()
    {
        return $this->toString();
    }
}
