<?php

namespace Fhp\Segment\IPS;

use Fhp\Segment\BaseDeg;

/**
 * Data element group: Unterstützte SEPA pain messages
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: D ("Unterstützte SEPA pain messages")
 */
class UnterstuetzteSEPAPainMessages extends BaseDeg
{
    /** @var string[] @Max(99) Max length: 256 */
    public array $sepaDescriptor;

    /** @param string[] $sepaDescriptor */
    public static function create(array $sepaDescriptor): UnterstuetzteSEPAPainMessages
    {
        $result = new UnterstuetzteSEPAPainMessages();
        $result->sepaDescriptor = array_values($sepaDescriptor);
        return $result;
    }
}
