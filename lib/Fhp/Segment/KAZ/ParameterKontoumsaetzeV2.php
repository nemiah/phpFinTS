<?php

namespace Fhp\Segment\KAZ;

/**
 * Segment: Kontoumsätze/Zeitraum Parameter (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: D (letter P under "Parameter Kontoumsätze/Zeitraum")
 */
class ParameterKontoumsaetzeV2 extends ParameterKontoumsaetzeV1 implements ParameterKontoumsaetze
{
    // NOTE: See ParameterKontoumsaetzeV1.

    /** @var bool */
    public $alleKontenErlaubt;

    /** @return bool */
    public function getAlleKontenErlaubt()
    {
        return $this->alleKontenErlaubt;
    }
}
