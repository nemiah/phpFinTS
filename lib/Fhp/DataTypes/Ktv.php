<?php

namespace Fhp\DataTypes;

/**
 * @link http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.3.1
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

    public function __construct(string $accountNumber, string $subAccountFeature, Kik $kik)
    {
        $this->accountNumber = $accountNumber;
        $this->subAccountFeature = $subAccountFeature;
        $this->kik = $kik;
    }

    public function toString(): string
    {
        return $this->accountNumber . ':' . $this->subAccountFeature . ':' . (string) $this->kik;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
