<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITAN;

use Fhp\DataTypes\Bin;
use Fhp\Segment\BaseSegment;

/**
 * Segment: Zwei-Schritt-TAN-Einreichung Rückmeldung (Version 6)
 * Bezugssegment: HKTAN
 * Sender: Kreditinstitut
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Page: 48
 */
class HITANv6 extends BaseSegment
{
    /**
     * 0,1,2 or 4
     * @var string
     */
    public $tanProzess;

    /**
     * M: bei Auftrags- Hashwertverfahren<>0 und TAN-Prozess=1,
     * N: sonst
     *
     * @var Bin|null
     */
    public $auftragsHashwert;

    /**
     * M: bei TAN-Prozess=2, 3, 4
     * O: bei TAN-Prozess=1
     * @var string|null
     */
    public $auftragsReferenz;

    /**
     * M: bei TAN-Prozess=1, 3, 4
     * O: bei TAN-Prozess=2
     *
     * Das Kundenprodukt muss den Inhalt der empfangenen Challenge dem Kunden unverändert anzeigen.
     * Ist der BPD-Parameter „Chal- lenge strukturiert“ mit „J“ belegt, so können im DE Challenge
     * Formatsteuerzeichen enthalten sein, die dann entsprechend zu inter- pretieren sind
     * (Näheres hierzu im Data Dictionary unter dem DE „Challenge“).
     * Erläuterung: Die Challenge kann institutsindividuell aufgebaut werden
     * (z. B. 1 oder 2 Eingabefelder für den chipTAN-Leser).
     *
     * @var string|null
     */
    public $challenge;

    /**
     * @var Bin|null
     */
    public $challengeHHD_UC;

    /**
     * TODO: This is a DEG
     *
     * @var string|null
     */
    public $gueltigkeitsdatumUndUhrzeitFuerChallenge;

    /**
     * M: bei TAN-Prozess=1, 3, 4 und „Anzahl unterstützter aktiver TAN-Medien“ nicht vorhanden
     * O: sonst
     * @var string|null
     */
    public $bezeichnungDesTanMediums;

    /** @return string|null */
    public function getAuftragsReferenz()
    {
        return $this->auftragsReferenz;
    }

    /** @return string|null */
    public function getChallenge()
    {
        return $this->challenge;
    }

    /** @return Bin|null */
    public function getChallengeHDD_UC()
    {
        return $this->challengeHHD_UC;
    }

    /** @return string|null */
    public function getBezeichnungDesTanMediums()
    {
        return $this->bezeichnungDesTanMediums;
    }
}
