<?php

namespace Fhp\DataTypes;

class Dat
{
    protected $value;

    public function __construct(\DateTime $dateTime)
    {
        $this->value = $dateTime;
    }

    public function setData(\DateTime $data)
    {
        $this->value = $data;
    }

    public function getData()
    {
        return $this->value;
    }

    public function toString()
    {
        return $this->value->format('Ymd');
    }

    public function __toString()
    {
        return $this->toString();
    }
}
