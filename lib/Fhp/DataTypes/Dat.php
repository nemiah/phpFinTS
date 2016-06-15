<?php

namespace Fhp\DataTypes;

/**
 * Class Dat
 * @package Fhp\DataTypes
 */
class Dat
{
    /**
     * @var \DateTime
     */
    protected $value;

    /**
     * Dat constructor.
     * @param \DateTime $dateTime
     */
    public function __construct(\DateTime $dateTime)
    {
        $this->value = $dateTime;
    }

    /**
     * @param \DateTime $date
     */
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
