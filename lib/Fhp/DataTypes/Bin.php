<?php

namespace Fhp\DataTypes;

class Bin
{
    protected $string;

    public function __construct($string)
    {
        $this->string = $string;
    }

    public function setData($data)
    {
        $this->string = $data;
    }

    public function getData()
    {
        return $this->string;
    }

    public function toString()
    {
        return '@' . strlen($this->string) . '@' . $this->string;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
