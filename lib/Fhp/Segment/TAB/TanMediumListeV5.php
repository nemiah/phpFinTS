<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAB;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: TAN-Medium-Liste (Version 5)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: D (letter T)
 */
class TanMediumListeV5 extends BaseDeg implements TanMediumListe
{
    /**
     * A: Alle Medien
     * L: Liste
     * G: TAN-Generator
     * M: Mobiltelefon mit mobileTAN
     * S: Secoder
     * B: Bilateral vereinbart
     */
    public string $tanMediumKlasse;
    /**
     * 1: Aktiv
     * 2: Verfügbar
     * 3: Aktiv Folgekarte
     * 4: Verfügbar Folgekarte
     */
    public int $status;
    /** Only for tanMediumKlasse=='B' */
    public ?int $sicherheitsfunktion = null;
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
    /** Must for tanMediumKlasse=='M', optional otherwise */
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
