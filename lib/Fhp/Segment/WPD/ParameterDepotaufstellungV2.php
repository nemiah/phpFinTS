<?php

namespace Fhp\Segment\WPD;

use Fhp\Segment\BaseDeg;
/**
 * Data Element Group: Parameter Depotauftstellung
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: O
 */
 */
class ParameterDepotaufstellungV2 extends BaseDeg implements ParameterDepotaufstellung
{
    /** @var bool */
    public $eingabeAnzahlEintraegeErlaubt;
	
	/** @var bool */
    public $waehrungDepotaufstellungWaehlbar;
	
	/** @var bool */
    public $kursqualitaetWaehlbar;

    /** @return bool */
    public function getEingabeAnzahlEintraegeErlaubt(): bool
    {
        return $this->eingabeAnzahlEintraegeErlaubt;
    }
	
	/** @return bool */
    public function getWaehrungDepotaufstellungWaehlbar(): bool
    {
        return $this->waehrungDepotaufstellungWaehlbar;
    }
	
	/** @return bool */
    public function getKursqualitaetWaehlbar(): bool
    {
        return $this->kursqualitaetWaehlbar;
    }
}
