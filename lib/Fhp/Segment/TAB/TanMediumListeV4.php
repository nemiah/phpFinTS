<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\TAB;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: TAN-Medium-Liste (Version 4).
 *
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: D (letter T)
 */
class TanMediumListeV4 extends BaseDeg implements TanMediumListe
{
    /**
     * A: Alle Medien
     * L: Liste
     * G: TAN-Generator
     * M: Mobiltelefon mit mobileTAN
     * S: Secoder.
     *
     * @var string
     */
    public $tanMediumKlasse;
    /**
     * 1: Aktiv
     * 2: Verfügbar
     * 3: Aktiv Folgekarte
     * 4: Verfügbar Folgekarte.
     *
     * @var int
     */
    public $status;
    /** @var string|null Only for $tanMediumKlasse=='G' */
    public $kartennummer;
    /** @var string|null Only for $tanMediumKlasse=='G' */
    public $kartenfolgenummer;
    /** @var int|null Only and optional for $tanMediumKlasse=='G' and if BPD allows it */
    public $kartenart;
    /** @var \Fhp\Segment\Common\KtvV3|null Only and optional for $tanMediumKlasse=='G' */
    public $kontoverbindungAuftraggeber;
    /** @var string|null JJJJMMTT gemäß ISO 8601 Only and optional for $tanMediumKlasse=='G' */
    public $gueltigAb;
    /** @var string|null JJJJMMTT gemäß ISO 8601 Only and optional for $tanMediumKlasse=='G' */
    public $gueltigBis;
    /** @var string|null Only for $tanMediumKlasse=='L' */
    public $tanListennumer;
    /** @var string|null Must for $tanMediumKlasse=='M', optional otherwise */
    public $bezeichnungDesTanMediums;
    /** @var string|null Only and optional for $tanMediumKlasse=='M' */
    public $mobiltelefonnummerVerschleiert;
    /** @var string|null Only and optional for $tanMediumKlasse=='M' */
    public $mobiltelefonnummer;
    /** @var \Fhp\Segment\Common\Kti|null Only and optional for $tanMediumKlasse=='M' */
    public $smsAbbuchungskonto;
    /** @var int|null */
    public $anzahlFreieTans;
    /** @var string|null JJJJMMTT gemäß ISO 8601 * */
    public $letzteBenutzung;
    /** @var string|null JJJJMMTT gemäß ISO 8601 * */
    public $freigeschaltetAm;

    /** {@inheritdoc} */
    public function getName()
    {
        return $this->bezeichnungDesTanMediums;
    }

    /** {@inheritdoc} */
    public function getPhoneNumber()
    {
        return null !== $this->mobiltelefonnummer ? $this->mobiltelefonnummer : $this->mobiltelefonnummerVerschleiert;
    }
}
