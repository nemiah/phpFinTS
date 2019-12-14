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
    public function getDate(): \DateTime
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->value->format('Ymd');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
