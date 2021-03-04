<?php

namespace Fhp\Segment\WPD;

use Fhp\Segment\BaseDeg;
/**
 * Data Element Group: Parameter Depotauftstellung
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
