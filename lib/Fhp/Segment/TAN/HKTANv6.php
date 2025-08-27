<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Geschäftsvorfall Zwei-Schritt-TAN-Einreichung (Version 6)
 *
 * @link: https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX1BJTlRBTl8yMDIwLTA3LTEwX2ZpbmFsX3ZlcnNpb24ucGRmIiwicGFnZSI6MTI3fQ.FJHEt1OwhZgDgfpwfO_ikZRn_hX8rbiSuesG2yyEle0/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
 * Section: B.5.1 a)
 */
class HKTANv6 extends BaseSegment implements HKTAN
{
    /**
     * Allowed values (ordered chronologically):
     * 1: In Prozessvariante 1 step 1, this HKTAN sends the hash ($auftragsHashwert) to the server to generate a
     *    challenge. In this case, the client does not send the main order (Auftrag) yet. The server will responds with
     *    a challenge in HITAN and the client sends the main order in step 2 (the server will recognize its hash) along
     *    with the TAN (response to the challenge from step 1).
     *
     * 3: (In Prozessvariante 2 step 1, HKTAN is used for Mehrfach-TANs. Not supported in this library).
     * 4: In Prozessvariante 2 step 1, this HKTAN is sent along with the main order to signal Prozessvariante 2 shall be
     *    used. The server responds with a challenge in HITAN.
     * 2: In Prozessvariante 2 step 2, this HKTAN contains a reference ($auftragsreferenz) to a previously posted order
     *    (from the server's HITAN). Along with this HKTAN the client sends the TAN (response to the challenge from
     *    step 1) in the same message. The server responds with the same reference (but no more challenge) to confirm
     *    that the TAN was accepted.
     * S: Only supported in HKTANv7 (which inherits this field). In Prozessvariante 2 for decoupled modes, instead of
     *    exectuting the step 2 above, the client polls the server regularly using step S to find out if the
     *    authentication process on the side channel (e.g. the user's smartphone) has completed. This HKTAN contains a
     *    reference ($auftragsreferenz) to a previously posted order (from the server's HITAN). The server responds with
     *    the same reference and a status code TODO.
     *
     * Note: It is not up to the application/library to choose this process, but rather it needs to execute the process
     * configured in the BPD ({@link VerfahrensparameterZweiSchrittVerfahren} field $tanProzess). In practice,
     * Prozessvariante 2 is much more common.
     *
     * Allowed values: 1 (for Prozessvariante 1), 2, 3, 4.
     *     NOTE: This field is re-used in HITANv7, where the value 'S' is also allowed.
     */
    public string $tanProzess;
    /**
     * M: bei TAN-Prozess=1
     * M: bei TAN-Prozess=4 und starker Authentifizierung
     * N: sonst
     * Max length: 6
     */
    public ?string $segmentkennung = null;
    /**
     * M: bei TAN-Prozess=1 und „Auftraggeberkonto erforderlich“=2 und Kontoverbindung im Auftrag enthalten
     * N: sonst
     */
    public ?\Fhp\Segment\Common\Kti $kontoverbindungInternationalAuftraggeber = null;
    /**
     * M: bei AuftragsHashwertverfahren<>0 und TAN-Prozess=1
     * N: sonst
     */
    public ?Bin $auftragsHashwert = null;
    /**
     * M: bei TAN-Prozess=2, 3 (and S)
     * O: TAN-Prozess=1, 4
     * Max length: 36
     */
    public ?string $auftragsreferenz = null;
    /**
     * M: bei TAN-Prozess=1, 2 (and S)
     * N: bei TAN-Prozess=3, 4
     */
    public ?bool $weitereTanFolgt = null;
    /**
     * O: bei TAN-Prozess=2 und „Auftragsstorno erlaubt“=J
     * N: sons
     */
    public ?bool $auftragStornieren = null;
    /**
     * M: bei TAN-Prozess=1, 3, 4 und „SMSAbbuchungskonto erforderlich“=2
     * O: sonst
     */
    public ?\Fhp\Segment\Common\Kti $smsAbbuchungskonto = null;
    /**
     * M: bei TAN-Prozess=1 und „Challenge-Klasse erforderlich“=J
     * N: sonst
     */
    public ?int $challengeKlasse = null;
    /**
     * O: bei TAN-Prozess=1 und „Challenge-Klasse erforderlich“=J
     * N: sonst
     */
    public ?ParameterChallengeKlasse $parameterChallengeKlasse = null;
    /**
     * Note: There are generally two ways to treat TAN media. Either the HITANS declares that multiple media are
     * available and the user has to choose one of them (possibly first using HKSPA to retrieve a list of options), in
     * which case the user's choice is sent here in HKTAN's $bezeichnungDesTanMediums, or the bank chooses on the user's
     * behalf and sends its choice in HITAN's $bezeichnungDesTanMediums.
     *
     * M: bei TAN-Prozess=1, 3, 4 und „Anzahl unterstützter aktiver TAN-Medien“ > 1
     *    und „Bezeichnung des TAN-Mediums erforderlich“=2
     * O: sonst
     * Max length 32
     */
    public ?string $bezeichnungDesTanMediums = null;
    /**
     * M: bei TAN-Prozess=2 und „Antwort HHD_UC erforderlich“=“J“
     * O: sonst
     */
    public ?AntwortHhdUc $antwortHhdUc = null;

    /**
     * @return HKTANv6 A dummy HKTANv6 segment to signal PSD2 readiness.
     */
    public static function createDummy(): HKTANv6
    {
        $result = HKTANv6::createEmpty();
        $result->tanProzess = HKTAN::TAN_PROZESS_4;
        $result->segmentkennung = 'HKIDN';
        return $result;
    }

    public function setTanProzess(string $tanProzess): void
    {
        $this->tanProzess = $tanProzess;
    }

    public function setSegmentkennung(?string $segmentkennung): void
    {
        $this->segmentkennung = $segmentkennung;
    }

    public function setBezeichnungDesTanMediums(?string $bezeichnungDesTanMediums): void
    {
        $this->bezeichnungDesTanMediums = $bezeichnungDesTanMediums;
    }

    public function setAuftragsreferenz(?string $auftragsreferenz): void
    {
        $this->auftragsreferenz = $auftragsreferenz;
    }

    public function setWeitereTanFolgt(?bool $weitereTanFolgt): void
    {
        $this->weitereTanFolgt = $weitereTanFolgt;
    }
}
