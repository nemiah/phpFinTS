<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Model\TanRequest;
use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Geschäftsvorfall Zwei-Schritt-TAN-Einreichung Rückmeldung (Version 6)
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: B.5.1 b)
 */
class HITANv6 extends BaseSegment implements TanRequest
{
    const DUMMY_REFERENCE = 'noref';

    /**
     * @var int Allowed values: 1 (for Prozessvariante 1), 2, 3, 4. See {@link HKTANv6#$tanProzess} for details.
     */
    public $tanProzess;
    /**
     * This will just return the same hash as was passed in HKTAN.
     * M: bei AuftragsHashwertverfahren<>0 und TAN-Prozess=1
     * N: sonst
     * @var Bin|null
     */
    public $auftragsHashwert;
    /**
     * Special value "noref" means that no TAN is needed.
     * M: bei TAN-Prozess=2, 3, 4
     * O: TAN-Prozess=1
     * @var string|null Max length: 35
     */
    public $auftragsreferenz;
    /**
     * This is the challenge that needs to be presented to the user, so that they can generate and enter a TAN.
     * Special value "nochallenge" means that no TAN is needed. If $challengeStrukturiert in HITANS is set, this may
     * contain certain HTML tags (br, p, b, i, u, ul, ol and li) that should ideally be rendered properly before
     * presenting the challenge to the user.
     *
     * M: bei TAN-Prozess=1, 3, 4
     * O: bei TAN-Prozess=2
     * @var string|null Max length: 2048
     */
    public $challenge;
    /** @var Bin|null */
    public $challengeHhdUc;
    /** @var GueltigkeitsdatumUndUhrzeitFuerChallenge|null */
    public $gueltigkeitsdatumUndUhrzeitFuerChallenge;
    /**
     * Note: There are generally two ways to treat TAN media, see also HKTAN's $bezeichnungDesTanMediums field. This
     * field here is set if the user does not choose the TAN medium beforehand, but the bank chooses it instead.
     *
     * M: bei TAN-Prozess=1, 3, 4 und „Anzahl unterstützter aktiver TAN-Medien“ nicht vorhanden
     * O: sonst
     * @var string|null Max length 32
     */
    public $bezeichnungDesTanMediums;

    /** {@inheritdoc} */
    public function getProcessId(): string
    {
        // Note: This is non-null because tanProzess==4.
        return $this->auftragsreferenz;
    }

    /** {@inheritdoc} */
    public function getChallenge(): string
    {
        // Note: This is non-null because tanProzess==4.
        return $this->challenge;
    }

    /** {@inheritdoc} */
    public function getTanMediumName(): ?string
    {
        return $this->bezeichnungDesTanMediums;
    }

    public function getTanProzess(): int
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
