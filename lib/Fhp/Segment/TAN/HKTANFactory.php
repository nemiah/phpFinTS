<?php

namespace Fhp\Segment\TAN;

use Fhp\Model\TanMode;
use Fhp\Segment\BaseSegment;

/**
 * Creates HKTAN segments matching the segment version used by the server.
 */
class HKTANFactory
{
    /**
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
     * @param TanMode $tanMode Parameters retrieved from the server during dialog initialization that describe how the
     *     TAN processes need to be parameterized.
     * @param string $auftragsreferenz The reference number received from the server in step 1 response (HITAN).
     * @return BaseSegment A HKTAN instance to tell the server the reference of the previously submitted order.
     */
    public static function createProzessvariante2Step2(TanMode $tanMode, string $auftragsreferenz): BaseSegment
    {
        $result = $tanMode->createHKTAN();
        $result->setTanProzess(HKTAN::TAN_PROZESS_2);
        $result->setAuftragsreferenz($auftragsreferenz);
        $result->setWeitereTanFolgt(false); // No Mehrfach-TAN support, so we'll never send true here.
        return $result;
    }
}
