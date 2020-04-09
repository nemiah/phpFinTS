<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\SAL;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\AccountInfo;

/**
 * Segment: Saldenabfrage (Version 6)
 *
 * There will be one segment instance per account.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.2.1 b)
 */
class HISALv6 extends BaseSegment implements HISAL
{
    /** @var \Fhp\Segment\Common\KtvV3 */
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
    /** @var \Fhp\Segment\Common\Btg|null This field can only be filled if $verfuegbarerBetrag is zero. */
    public $ueberziehung;
    /** @var \Fhp\Segment\Common\Tsp|null */
    public $buchungszeitpunkt;
    /** @var string|null JJJJMMTT gemäß ISO 8601 */
    public $faelligkeit;

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
