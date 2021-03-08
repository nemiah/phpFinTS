<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SAL;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\AccountInfo;

/**
 * Segment: Saldenabfrage (Version 4)
 *
 * There will be one segment instance per account.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI21o.pdf
 * Section: VII.2.2 b)
 */
class HISALv4 extends BaseSegment implements HISAL
{
    /** @var \Fhp\Segment\Common\Kto */
    public $kontoverbindungAuftraggeber;
    /** @var string */
    public $kontoproduktbezeichnung;
    /** @var string */
    public $kontowaehrung;
    /** @var \Fhp\Segment\Common\Sdo */
    public $gebuchterSaldo;
    /** @var \Fhp\Segment\Common\Sdo|null */
    public $saldoDerVorgemerktenUmsaetze;
    /** @var \Fhp\Segment\Common\Btg|null */
    public $kreditlinie;
    /** @var \Fhp\Segment\Common\Btg|null */
    public $verfuegbarerBetrag;
    /** @var \Fhp\Segment\Common\Btg|null */
    public $bereitsVerfuegterBetrag;
    /** @var string|null JJJJMMTT gemäß ISO 8601 */
    public $buchungsdatumDesSaldos;
    /** @var string|null hhmmss gemäß ISO 8601, local time (no time zone support). */
    public $buchungsuhrzeitDesSaldos;

    public function getAccountInfo(): AccountInfo
    {
        return $this->kontoverbindungAuftraggeber;
    }

    public function getKontoproduktbezeichnung(): string
    {
        return $this->kontoproduktbezeichnung;
    }

    public function getGebuchterSaldo(): \Fhp\Segment\Common\Sdo
    {
        return $this->gebuchterSaldo;
    }

    public function getSaldoDerVorgemerktenUmsaetze(): ?\Fhp\Segment\Common\Sdo
    {
        return $this->saldoDerVorgemerktenUmsaetze;
    }

    public function getKreditlinie(): ?\Fhp\Segment\Common\Btg
    {
        return $this->kreditlinie;
    }

    public function getVerfuegbarerBetrag(): ?\Fhp\Segment\Common\Btg
    {
        return $this->verfuegbarerBetrag;
    }

    public function getBereitsVerfuegterBetrag(): ?\Fhp\Segment\Common\Btg
    {
        return $this->bereitsVerfuegterBetrag;
    }

    public function getBuchungszeitpunkt(): ?\Fhp\Segment\Common\Tsp
    {
        return $this->buchungsdatumDesSaldos === null ? null :
            \Fhp\Segment\Common\Tsp::create($this->buchungsdatumDesSaldos, $this->buchungsuhrzeitDesSaldos);
    }

    public function getFaelligkeit(): ?string
    {
        return null;
    }
}
