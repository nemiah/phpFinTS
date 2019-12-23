<?php

namespace Fhp\Segment\SPA;

/**
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: D ("Unterstützte SEPA-Datenformate")
 */
trait GetUnterstuetzteSepaDatenformateTrait
{
    /** @return string[] */
    public function getUnterstuetzteSepaDatenformate(): array
    {
        // Something like "sepade.pain.00x.001.0y.xsd" is allowed here, which is not a valid SEPA XML URN / Namespace
        return array_map(function ($sepaUrn) {
            return strtr($sepaUrn, [
                'sepade:' => 'urn:iso:std:iso:20022:tech:',
                '.xsd' => '',
            ]);
        }, $this->unterstuetzteSepaDatenformate);
    }
}
