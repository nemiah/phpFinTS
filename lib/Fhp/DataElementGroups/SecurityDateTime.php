<?php

namespace Fhp\DataElementGroups;

use Fhp\Deg;

/**
 * Class SecurityDateTime.
 * @package Fhp\DataElementGroups
 */
class SecurityDateTime extends Deg
{
    /**
     *  Sicherheitszeitstempel (STS)
     */
    const DATETIME_TYPE_STS = 1;

    /**
     * Certificate Revocation Time (CRT)
     */
    const DATETIME_TYPE_CRT = 6;

    /**
     * SecurityDateTime constructor.
     *
     * @param int $type
     * @param \DateTime|null $dateTime
     */
    public function __construct($type = self::DATETIME_TYPE_STS, \DateTime $dateTime = null)
    {
        $date = null == $dateTime ? new \DateTime() : $dateTime;
        $this->addDataElement($type);
        $this->addDataElement($date->format('Ymd'));
        $this->addDataElement($date->format('His'));
    }
}
