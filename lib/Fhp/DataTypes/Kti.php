<?php

namespace Fhp\DataTypes;

/**
 * Class Kti (Kontoverbindung International)
 *
 * @link http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.3.2
 * @package Fhp\DataTypes
 */
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
    public function __construct($iban, $bic, $accountNumber, $subAccountFeature, Kik $kik)
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
    public function toString()
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
    public function __toString()
    {
        return $this->toString();
    }
}
