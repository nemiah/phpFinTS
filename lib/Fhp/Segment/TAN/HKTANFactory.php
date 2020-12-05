<?php

namespace Fhp\Segment\TAN;

use Fhp\Model\TanMode;
use Fhp\Segment\BaseSegment;

/**
 * Creates HKTAN segments matching the segment version used by the server.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
 * Section D (look for "TAN-Prozess" version 1)
 */
class HKTANFactory
{
    /**
     * This is TAN-Prozess=4, which is the first step of Prozessvariante 2. In this step, the client application sends
     * a main action segment together with a HKTAN segment, in order to indicate that it is prepared to authenticate the
     * action with a TAN if the server asks for it (which would trigger step 2 below).
     *
     * @param TanMode $tanMode Parameters retrieved from the server during dialog initialization that describe how the
     *     TAN processes need to be parameterized.
     * @param ?string $tanMedium The TAN medium selected by the user. Mandatory if $tanMode is present and requires a
     *     TAN medium.
     * @param string $segmentkennung The segment that we want to authenticate with the HKTAN instance.
     * @return BaseSegment A HKTAN instance to signal to the server that Prozessvariante 2 shall be used.
     */
    public static function createProzessvariante2Step1(TanMode $tanMode, ?string $tanMedium = null, string $segmentkennung = 'HKIDN'): BaseSegment
    {
        if ($tanMode !== null && $tanMode->getSmsAbbuchungskontoErforderlich()) {
            throw new \InvalidArgumentException('SMS-Abbuchungskonto not supported');
        }

        $result = $tanMode->createHKTAN();
        $result->setTanProzess(HKTAN::TAN_PROZESS_4);
        $result->setSegmentkennung($segmentkennung);
        if ($tanMode !== null && $tanMode->needsTanMedium()) {
            if ($tanMedium === null) {
                throw new \InvalidArgumentException('Missing tanMedium');
            }
            $result->setBezeichnungDesTanMediums($tanMedium);
        }
        return $result;
    }

    /**
     * This is TAN-Prozess=2, which is the second step of Prozessvariante 2. If the bank server asked for a TAN in step
     * 1 above, then the client application sends that TAN in a HKTAN segment to the server in order to authenticate the
     * previously transmitted action.
     *
     * @param TanMode $tanMode Parameters retrieved from the server during dialog initialization that describe how the
     *     TAN processes need to be parameterized.
     * @param string $auftragsreferenz The reference number received from the server in step 1 response (HITAN).
     * @return BaseSegment A HKTAN instance to tell the server the reference of the previously submitted action.
     */
    public static function createProzessvariante2Step2(TanMode $tanMode, string $auftragsreferenz): BaseSegment
    {
        $result = $tanMode->createHKTAN();
        $result->setTanProzess(HKTAN::TAN_PROZESS_2);
        $result->setAuftragsreferenz($auftragsreferenz);
        $result->setWeitereTanFolgt(false); // No Mehrfach-TAN support, so we'll never send true here.
        return $result;
    }

    /**
     * This is TAN-Prozess=S, which is an alternative, repeated step 2 of Prozessvariante 2 for decoupled TAN modes.
     * The TAN mode being "decoupled" means that the challenge and TAN submission (or simply transaction confirmation)
     * happen entirely on the side channel (e.g. on the user's phone) and don't involve the application that triggered
     * the action (i.e. the application using phpFinTs). This means that the application never submits a TAN (never
     * calls {@link createProzessvariante2Step2()}). In order to learn when the authentication has completed, the
     * application can use this process step 'S' to poll the server.
     * @param TanMode $tanMode Parameters retrieved from the server during dialog initialization that describe how the
     *     TAN processes need to be parameterized. Must be a "decoupled" mode.
     * @param string $auftragsreferenz The reference number received from the server in step 1 response (HITAN).
     * @return BaseSegment A HKTAN instance to ask the server about the authentication status of the previously
     *     submitted action.
     */
    public static function createProzessvariante2StepS(TanMode $tanMode, string $auftragsreferenz): BaseSegment
    {
        if (!$tanMode->isDecoupled()) {
            throw new \InvalidArgumentException('Cannot use step S with non-decoupled TAN mode');
        }
        $result = $tanMode->createHKTAN();
        if ($result->getVersion() < 7) {
            throw new \InvalidArgumentException('Step S is only supported with HKTAN version 7+');
        }
        $result->setTanProzess(HKTAN::TAN_PROZESS_S);
        $result->setAuftragsreferenz($auftragsreferenz);
        $result->setWeitereTanFolgt(false); // No Mehrfach-TAN support, so we'll never send true here.
        return $result;
    }
}
