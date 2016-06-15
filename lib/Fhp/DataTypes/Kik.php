<?php

namespace Fhp\DataTypes;

/**
 * Class Kik
 * @package Fhp\DataTypes
 */
class Kik
{
    /**
     * @var string
     */
    protected $countryCode;

    /**
     * @var string
     */
    protected $bankCode;

    /**
     * Kik constructor.
     *
     * @param string $countryCode
     * @param string $bankCode
     */
    public function __construct($countryCode, $bankCode)
    {
        $this->countryCode = (string) $countryCode;
        $this->bankCode = (string) $bankCode;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->countryCode . ':' . $this->bankCode;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
