<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAB;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: TAN-Medium-Liste (Version 4)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX1BJTlRBTl8yMDIwLTA3LTEwX2ZpbmFsX3ZlcnNpb24ucGRmIiwicGFnZSI6MTI3fQ.FJHEt1OwhZgDgfpwfO_ikZRn_hX8rbiSuesG2yyEle0/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
 * Section: D (letter T)
 */
class TanMediumListeV4 extends BaseDeg implements TanMediumListe
{
    /**
     * A: Alle Medien
     * L: Liste
     * G: TAN-Generator
     * M: Mobiltelefon mit mobileTAN
     * S: Secoder
     */
    public string $tanMediumKlasse;
    /**
     * 1: Aktiv
     * 2: Verfügbar
     * 3: Aktiv Folgekarte
     * 4: Verfügbar Folgekarte
     */
    public int $status;
    /** Only for tanMediumKlasse=='G' */
    public ?string $kartennummer = null;
    /** Only for tanMediumKlasse=='G' */
    public ?string $kartenfolgenummer = null;
    /** Only and optional for tanMediumKlasse=='G' and if BPD allows it */
    public ?int $kartenart = null;
    /** Only and optional for tanMediumKlasse=='G' */
    public ?\Fhp\Segment\Common\KtvV3 $kontoverbindungAuftraggeber = null;
    /** JJJJMMTT gemäß ISO 8601 Only and optional for tanMediumKlasse=='G' */
    public ?string $gueltigAb = null;
    /** JJJJMMTT gemäß ISO 8601 Only and optional for tanMediumKlasse=='G' */
    public ?string $gueltigBis = null;
    /** Only for tanMediumKlasse=='L' */
    public ?string $tanListennumer = null;
    /** Must for tanMediumKlasse=='M', optional otherwise. Max length: 32 */
    public ?string $bezeichnungDesTanMediums = null;
    /** Only and optional for tanMediumKlasse=='M' */
    public ?string $mobiltelefonnummerVerschleiert = null;
    /** Only and optional for tanMediumKlasse=='M' */
    public ?string $mobiltelefonnummer = null;
    /** Only and optional for tanMediumKlasse=='M' */
    public ?\Fhp\Segment\Common\Kti $smsAbbuchungskonto = null;
    public ?int $anzahlFreieTans = null;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $letzteBenutzung = null;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $freigeschaltetAm = null;

    /** {@inheritdoc} */
    public function getName(): string
    {
        return $this->bezeichnungDesTanMediums;
    }

    /** {@inheritdoc} */
    public function getPhoneNumber(): ?string
    {
        return $this->mobiltelefonnummer !== null ? $this->mobiltelefonnummer : $this->mobiltelefonnummerVerschleiert;
    }
}
