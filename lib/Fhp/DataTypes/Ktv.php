<?php

namespace Fhp\DataTypes;

/**
 * Class Ktv (Kontoverbindung)
 *
 * @link http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.3.1
 * @package Fhp\DataTypes
 */
class Ktv
{
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
     * Ktv constructor.
     *
     * @param string $accountNumber
     * @param string $subAccountFeature
     * @param Kik $kik
     */
    public function __construct($accountNumber, $subAccountFeature, Kik $kik)
    {
        $this->accountNumber = $accountNumber;
        $this->subAccountFeature = $subAccountFeature;
        $this->kik = $kik;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->accountNumber . ':' . $this->subAccountFeature . ':' . (string) $this->kik;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
