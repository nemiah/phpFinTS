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
    public \Fhp\Segment\Common\Kto $kontoverbindungAuftraggeber;
    public string $kontoproduktbezeichnung;
    public string $kontowaehrung;
    public \Fhp\Segment\Common\Sdo $gebuchterSaldo;
    public ?\Fhp\Segment\Common\Sdo $saldoDerVorgemerktenUmsaetze = null;
    public ?\Fhp\Segment\Common\Btg $kreditlinie = null;
    public ?\Fhp\Segment\Common\Btg $verfuegbarerBetrag = null;
    public ?\Fhp\Segment\Common\Btg $bereitsVerfuegterBetrag = null;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $buchungsdatumDesSaldos = null;
    /** hhmmss gemäß ISO 8601, local time (no time zone support). */
    public ?string $buchungsuhrzeitDesSaldos = null;

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
