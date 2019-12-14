<?php

namespace Fhp\DataTypes;

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
    public function __construct(string $countryCode, string $bankCode)
    {
        $this->countryCode = (string) $countryCode;
        $this->bankCode = (string) $bankCode;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->countryCode . ':' . $this->bankCode;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
