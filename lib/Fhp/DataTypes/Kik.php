<?php

namespace Fhp\DataTypes;

class Kik
{
    protected $countryCode;
    protected $bankCode;

    public function __construct($countryCode, $bankCode)
    {
        $this->countryCode = (string) $countryCode;
        $this->bankCode = (string) $bankCode;
    }

    public function toString()
    {
        return $this->countryCode . ':' . $this->bankCode;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
