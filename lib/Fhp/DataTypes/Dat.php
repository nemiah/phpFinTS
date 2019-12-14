<?php

namespace Fhp\DataTypes;

class Dat
{
    /**
     * @var \DateTime
     */
    protected $value;

    /**
     * Dat constructor.
     */
    public function __construct(\DateTime $dateTime)
    {
        $this->value = $dateTime;
    }

    public function setDate(\DateTime $date)
    {
        $this->value = $date;
    }

    public function getDate(): \DateTime
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value->format('Ymd');
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
