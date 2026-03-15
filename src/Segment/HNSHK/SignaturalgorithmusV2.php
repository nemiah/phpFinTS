<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNSHK;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Signaturalgorithmus (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section: D (letter S)
 */
class SignaturalgorithmusV2 extends BaseDeg
{
    /**
     * 6: Owner Signing (OSG)
     */
    public int $verwendungDesSignaturalgorithmus = 6; // The only allowed value.
    /**
     * 1: nicht zugelassen
     * 10: RSA-Algorithmus (bei RAH)
     */
    public int $signaturalgorithmus = 10; // The only allowed value (not actually used, just as a dummy for PIN/TAN).
    /**
     * 19: RSASSA-PSS (bei RAH, RDH)
     */
    public int $operationsmodus = 19; // The only allowed value (not actually used, just as a dummy for PIN/TAN).
}
