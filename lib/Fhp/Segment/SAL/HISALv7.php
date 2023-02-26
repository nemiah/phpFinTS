<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SAL;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\AccountInfo;

/**
 * Segment: Saldenabfrage (Version 7)
 *
 * There will be one segment instance per account.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.2.2 b)
 */
class HISALv7 extends BaseSegment implements HISAL
{
    public \Fhp\Segment\Common\Kti $kontoverbindungInternational;
    public string $kontoproduktbezeichnung;
    public string $kontowaehrung;
    public \Fhp\Segment\Common\Sdo $gebuchterSaldo;
    public ?\Fhp\Segment\Common\Sdo $saldoDerVorgemerktenUmsaetze = null;
    public ?\Fhp\Segment\Common\Btg $kreditlinie = null;
    public ?\Fhp\Segment\Common\Btg $verfuegbarerBetrag = null;
    public ?\Fhp\Segment\Common\Btg $bereitsVerfuegterBetrag = null;
    /** This field can only be filled if {@link HISALv7::$verfuegbarerBetrag} is zero. */
    public ?\Fhp\Segment\Common\Btg $ueberziehung = null;
    public ?\Fhp\Segment\Common\Tsp $buchungszeitpunkt = null;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $faelligkeit = null;

    public function getAccountInfo(): AccountInfo
    {
        return $this->kontoverbindungInternational;
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
        return $this->buchungszeitpunkt;
    }

    public function getFaelligkeit(): ?string
    {
        return $this->faelligkeit;
    }
}
