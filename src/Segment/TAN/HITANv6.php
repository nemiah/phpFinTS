<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Geschäftsvorfall Zwei-Schritt-TAN-Einreichung Rückmeldung (Version 6)
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: B.5.1 b)
 */
class HITANv6 extends BaseSegment implements HITAN
{
    /**
     * Allowed values: 1 (for Prozessvariante 1), 2, 3, 4. See {@link HKTANv6::$tanProzess} for details.
     *     NOTE: This field is re-used in HITANv7, where the value 'S' is also allowed.
     */
    public string $tanProzess;
    /**
     * This will just return the same hash as was passed in HKTAN.
     * M: bei AuftragsHashwertverfahren<>0 und TAN-Prozess=1
     * N: sonst
     */
    public ?Bin $auftragsHashwert = null;
    /**
     * Special value "noref" means that no TAN is needed.
     * M: bei TAN-Prozess=2, 3, 4 (and S)
     * O: TAN-Prozess=1
     * Max length: 35
     */
    public ?string $auftragsreferenz = null;
    /**
     * This is the challenge that needs to be presented to the user, so that they can generate and enter a TAN.
     * Special value "nochallenge" means that no TAN is needed. If $challengeStrukturiert in HITANS is set, this may
     * contain certain HTML tags (br, p, b, i, u, ul, ol and li) that should ideally be rendered properly before
     * presenting the challenge to the user.
     *
     * M: bei TAN-Prozess=1, 3, 4
     * O: bei TAN-Prozess=2 (and S)
     * Max length: 2048
     */
    public ?string $challenge = null;
    public ?Bin $challengeHhdUc = null;
    public ?GueltigkeitsdatumUndUhrzeitFuerChallenge $gueltigkeitsdatumUndUhrzeitFuerChallenge = null;
    /**
     * Note: There are generally two ways to treat TAN media, see also HKTAN's $bezeichnungDesTanMediums field. This
     * field here is set if the user does not choose the TAN medium beforehand, but the bank chooses it instead.
     *
     * M: bei TAN-Prozess=1, 3, 4 und „Anzahl unterstützter aktiver TAN-Medien“ nicht vorhanden
     * O: sonst
     * Max length 32
     */
    public ?string $bezeichnungDesTanMediums = null;

    public function getProcessId(): string
    {
        // Note: This is non-null because tanProzess==4.
        return $this->auftragsreferenz;
    }

    public function getChallenge(): ?string
    {
        // Note: This is non-null because tanProzess==4.
        return $this->challenge === static::DUMMY_CHALLENGE ? null : $this->challenge;
    }

    public function getTanMediumName(): ?string
    {
        return $this->bezeichnungDesTanMediums;
    }

    public function getTanProzess(): string
    {
        return $this->tanProzess;
    }

    public function getAuftragsHashwert(): ?Bin
    {
        return $this->auftragsHashwert;
    }

    public function getAuftragsreferenz(): ?string
    {
        return $this->auftragsreferenz;
    }

    public function getChallengeHhdUc(): ?Bin
    {
        return $this->challengeHhdUc;
    }

    public function getGueltigkeitsdatumUndUhrzeitFuerChallenge(): ?GueltigkeitsdatumUndUhrzeitFuerChallenge
    {
        return $this->gueltigkeitsdatumUndUhrzeitFuerChallenge;
    }

    public function getBezeichnungDesTanMediums(): ?string
    {
        return $this->bezeichnungDesTanMediums;
    }
}
