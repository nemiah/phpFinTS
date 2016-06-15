<?php

namespace Fhp\DataElementGroups;

use Fhp\DataTypes\Kik;
use Fhp\Deg;

/**
 * Class KeyName.
 * @package Fhp\DataElementGroups
 */
class KeyName extends Deg
{
    const KEY_TYPE_DS_KEY = 'D';
    const KEY_TYPE_SIGNATURE = 'S';
    const KEY_TYPE_CHIFFRE = 'V';

    /**
     * KeyName constructor.
     *
     * @param string $countryCode
     * @param string $bankCode
     * @param string $userName
     * @param string $keyType
     */
    public function __construct($countryCode, $bankCode, $userName, $keyType = self::KEY_TYPE_CHIFFRE)
    {
        $kik = new Kik($countryCode, $bankCode);
        $this->addDataElement($kik->toString());
        $this->addDataElement($userName);
        $this->addDataElement($keyType);
        $this->addDataElement(0);
        $this->addDataElement(0);
    }
}
