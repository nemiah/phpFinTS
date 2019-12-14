<?php

namespace Fhp\DataTypes;

class Kti
{
    /**
     * @var string
     */
    protected $iban;

    /**
     * @var string
     */
    protected $bic;

    /**
     * @var string
     */
    protected $accountNumber;

    /**
     * @var string
     */
    protected $subAccountFeature;

    /**
     * @var Kik
     */
    protected $kik;

    /**
     * Kti constructor.
     *
     * @param string $iban
     * @param string $bic
     * @param string $accountNumber
     * @param string $subAccountFeature
     * @param Kik $kik
     */
    public function __construct(string $iban, string $bic, string $accountNumber, string $subAccountFeature, Kik $kik)
    {
        $this->iban = $iban;
        $this->bic = $bic;
        $this->accountNumber = $accountNumber;
        $this->subAccountFeature = $subAccountFeature;
        $this->kik = $kik;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->iban . ':'
            . $this->bic . ':'
            . $this->accountNumber . ':'
            . $this->subAccountFeature . ':'
            . (string) $this->kik;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
