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

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->value->format('Ymd');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
