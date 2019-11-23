<?php

namespace Fhp\Segment\HKEND;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Dialogende (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: C.4.2.2
 */
class HKENDv1 extends BaseSegment
{
    /** @var string */
    public $dialogId;

    /**
     * @param string $dialogId
     * @return HKENDv1
     */
    public static function create($dialogId)
    {
        $result = HKENDv1::createEmpty();
        $result->dialogId = $dialogId;
        return $result;
    }
}
