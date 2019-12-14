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
     */
    public function __construct(string $countryCode, string $bankCode)
    {
        $this->countryCode = (string) $countryCode;
        $this->bankCode = (string) $bankCode;
    }

    public function toString(): string
    {
        return $this->countryCode . ':' . $this->bankCode;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
