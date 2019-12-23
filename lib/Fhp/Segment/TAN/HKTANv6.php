<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\DataTypes\Bin;
use Fhp\Model\TanMode;
use Fhp\Segment\BaseSegment;

/**
 * Segment: Geschäftsvorfall Zwei-Schritt-TAN-Einreichung (Version 6)
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: B.5.1 a)
 */
class HKTANv6 extends BaseSegment
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
     *
     * Note: It is not up to the application/library to choose this process, but rather it needs to execute the process
     * configured in the BPD ({@link VerfahrensparameterZweiSchrittVerfahren} field $tanProzess). In practice,
     * Prozessvariante 2 is much more common.
     *
     * @var int Allowed values: 1 (for Prozessvariante 1), 2, 3, 4
     */
    public $tanProzess;
    /**
     * M: bei TAN-Prozess=1
     * M: bei TAN-Prozess=4 und starker Authentifizierung
     * N: sonst
     * @var string|null Max length: 6
     */
    public $segmentkennung;
    /**
     * M: bei TAN-Prozess=1 und „Auftraggeberkonto erforderlich“=2 und Kontoverbindung im Auftrag enthalten
     * N: sonst
     * @var \Fhp\Segment\Common\Kti|null
     */
    public $kontoverbindungInternationalAuftraggeber;
    /**
     * M: bei AuftragsHashwertverfahren<>0 und TAN-Prozess=1
     * N: sonst
     * @var Bin|null
     */
    public $auftragsHashwert;
    /**
     * M: bei TAN-Prozess=2, 3
     * O: TAN-Prozess=1, 4
     * @var string|null Max length: 36
     */
    public $auftragsreferenz;
    /**
     * M: bei TAN-Prozess=1, 2
     * N: bei TAN-Prozess=3, 4
     * @var bool|null
     */
    public $weitereTanFolgt;
    /**
     * O: bei TAN-Prozess=2 und „Auftragsstorno erlaubt“=J
     * N: sons
     * @var bool|null
     */
    public $auftragStornieren;
    /**
     * M: bei TAN-Prozess=1, 3, 4 und „SMSAbbuchungskonto erforderlich“=2
     * O: sonst
     * @var \Fhp\Segment\Common\Kti|null
     */
    public $smsAbbuchungskonto;
    /**
     * M: bei TAN-Prozess=1 und „Challenge-Klasse erforderlich“=J
     * N: sonst
     * @var int|null
     */
    public $challengeKlasse;
    /**
     * O: bei TAN-Prozess=1 und „Challenge-Klasse erforderlich“=J
     * N: sonst
     * @var ParameterChallengeKlasse|null
     */
    public $parameterChallengeKlasse;
    /**
     * Note: There are generally two ways to treat TAN media. Either the HITANS declares that multiple media are
     * available and the user has to choose one of them (possibly first using HKSPA to retrieve a list of options), in
     * which case the user's choice is sent here in HKTAN's $bezeichnungDesTanMediums, or the bank chooses on the user's
     * behalf and sends its choice in HITAN's $bezeichnungDesTanMediums.
     *
     * M: bei TAN-Prozess=1, 3, 4 und „Anzahl unterstützter aktiver TAN-Medien“ > 1
     *    und „Bezeichnung des TAN-Mediums erforderlich“=2
     * O: sonst
     * @var string|null Max length 32
     */
    public $bezeichnungDesTanMediums;
    /**
     * M: bei TAN-Prozess=2 und „Antwort HHD_UC erforderlich“=“J“
     * O: sonst
     * @var AntwortHhdUc|null
     */
    public $antwortHhdUc;

    /**
     * @param TanMode|null $tanMode Parameters retrieved from the server during dialog initialization that describe how
     *     the TAN processes need to be parameterized.
     * @param string|null $tanMedium The TAN medium selected by the user. Mandatory if $tanMode is present and requires
     *     a TAN medium.
     * @param string $segmentkennung The segment that we want to authenticate with the HKTAN instance.
     * @return HKTANv6 A HKTAN instance to signal to the server that Prozessvariante 2 shall be used.
     */
    public static function createProzessvariante2Step1(?TanMode $tanMode = null, ?string $tanMedium = null, string $segmentkennung = 'HKIDN'): HKTANv6
    {
        // TODO: Implement the inclusion of the account for which an action is called, in the HKTAN Segment
        // A lot of Banks announce they need the account when in fact they work fine without it.
        /*if ($tanMode !== null && $tanMode->getAuftraggeberkontoErforderlich()) {
            throw new \InvalidArgumentException('Auftraggeberkonto not supported');
        }*/
        if ($tanMode !== null && $tanMode->getSmsAbbuchungskontoErforderlich()) {
            throw new \InvalidArgumentException('SMS-Abbuchungskonto not supported');
        }

        $result = HKTANv6::createEmpty();
        $result->tanProzess = 4;
        $result->segmentkennung = $segmentkennung;
        if ($tanMode !== null && $tanMode->needsTanMedium()) {
            if ($tanMedium === null) {
                throw new \InvalidArgumentException('Missing tanMedium');
            }
            $result->bezeichnungDesTanMediums = $tanMedium;
        }
        return $result;
    }

    /**
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
     * Section: B.4.3.1
     * @param string $segmentkennung The name of the main business transaction segment that shall be executed in the
     *     dialog. Must be one of the segments whitelisted for weak authentication (see the specification linked above).
     * @return HKTANv6 A HKTAN instance to signal to the server that the client supports strong authentication but wants
     *     to use weak authentication in this dialog, which only consists of the special business transaction.
     */
    public static function createWeakAuthenticationFor(string $segmentkennung): HKTANv6
    {
        $result = HKTANv6::createProzessvariante2Step1();
        $result->segmentkennung = $segmentkennung;
        return $result;
    }

    /**
     * @param TanMode $params Parameters retrieved from the server during dialog initialization that describe how the
     *     TAN processes need to be parameterized.
     * @param string $auftragsreferenz The reference number received from the server in step 1 response (HITAN).
     * @return HKTANv6 A HKTAN instance to tell the server the reference of the previously submitted order.
     */
    public static function createProzessvariante2Step2(TanMode $params, string $auftragsreferenz): HKTANv6
    {
        if ($params->getAntwortHhdUcErforderlich()) {
            // TODO Implement photoTAN support.
            // TODO Consorsbank sets this despite not actually requiring it.
            //throw new \InvalidArgumentException("HHD_UC not supported");
        }

        $result = HKTANv6::createEmpty();
        $result->tanProzess = 2;
        $result->auftragsreferenz = $auftragsreferenz;
        $result->weitereTanFolgt = false; // No Mehrfach-TAN support, so we'll never send true here.
        return $result;
    }
}
