<?php

namespace Fhp\Segment;

/**
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: D ("Unterstützte SEPA-Datenformate")
 */
trait UnterstuetzteSEPADatenformateTrait
{
    /** @return string[] */
    public function getUnterstuetzteSEPADatenformate(): array
    {
        // Something like "sepade.pain.00x.001.0y.xsd" is allowed here, which is not a valid SEPA XML URN / Namespace
        return array_map(function ($sepaUrn) {
            return strtr($sepaUrn, [
                'sepade:xsd:' => 'urn:iso:std:iso:20022:tech:xsd:',
                'sepade:' => 'urn:iso:std:iso:20022:tech:xsd:',
                '.xsd' => '',
            ]);
        }, $this->unterstuetzteSepaDatenformate ?? $this->unterstuetzteSEPADatenformate ?? []);
    }
}
