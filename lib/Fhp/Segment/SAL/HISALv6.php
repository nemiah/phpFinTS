<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SAL;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\AccountInfo;

/**
 * Segment: Saldenabfrage (Version 6)
 *
 * There will be one segment instance per account.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.2.1.2.1 b)
 */
class HISALv6 extends BaseSegment implements HISAL
{
    public \Fhp\Segment\Common\KtvV3 $kontoverbindungAuftraggeber;
    public string $kontoproduktbezeichnung;
    public string $kontowaehrung;
    public \Fhp\Segment\Common\Sdo $gebuchterSaldo;
    public ?\Fhp\Segment\Common\Sdo $saldoDerVorgemerktenUmsaetze = null;
    public ?\Fhp\Segment\Common\Btg $kreditlinie = null;
    public ?\Fhp\Segment\Common\Btg $verfuegbarerBetrag = null;
    public ?\Fhp\Segment\Common\Btg $bereitsVerfuegterBetrag = null;
    /** This field can only be filled if {@link HISALv6::$verfuegbarerBetrag} is zero. */
    public ?\Fhp\Segment\Common\Btg $ueberziehung = null;
    public ?\Fhp\Segment\Common\Tsp $buchungszeitpunkt = null;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $faelligkeit = null;

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
        return $this->buchungszeitpunkt;
    }

    public function getFaelligkeit(): ?string
    {
        return $this->faelligkeit;
    }
}
