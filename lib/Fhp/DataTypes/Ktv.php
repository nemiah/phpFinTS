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
    protected $accountNumber;
    protected $subAccountFeature;
    protected $kik;

    public function __construct($accountNumber, $subAccountFeature, Kik $kik)
    {
        $this->accountNumber = $accountNumber;
        $this->subAccountFeature = $subAccountFeature;
        $this->kik = $kik;
    }

    public function toString()
    {
        return $this->accountNumber . ':' . $this->subAccountFeature . ':' . (string) $this->kik;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
