<?php

namespace Fhp\Segment\CAZ;

use Fhp\Segment\BaseDeg;

/**
 * Segment: Parameter Kontoumsätze/Zeitraum camt
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: D (letter U under "Unterstützte camt-messages")
 */
class UnterstuetzteCamtMessages extends BaseDeg
{
    /** @var string[] @Max(99) */
    public $camtDescriptor;

    public static function create(array $camtDescriptor)
    {
        $result = new UnterstuetzteCamtMessages();
        $result->camtDescriptor = $camtDescriptor;
        return $result;
    }
}
