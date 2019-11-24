<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseDeg;

class ParameterZweiSchrittTanEinreichungV3 extends BaseDeg implements ParameterZweiSchrittTanEinreichung
{
    /** @var bool */
    public $einschrittVerfahrenErlaubt;
    /** @var bool */
    public $mehrAlsEinTanPflichtigerAuftragProNachrichtErlaubt;
    /**
     * 0: Auftrags-Hashwert nicht unterstützt
     * 1: RIPEMD-160
     * 2: SHA-1.
     *
     * @var int
     */
    public $auftragsHashwertverfahren;
    /** @var VerfahrensparameterZweiSchrittVerfahrenV3[] @Max(98) */
    public $verfahrensparameterZweiSchrittVerfahren;

    /** @return VerfahrensparameterZweiSchrittVerfahrenV3[] */
    public function getVerfahrensparameterZweiSchrittVerfahren()
    {
        return $this->verfahrensparameterZweiSchrittVerfahren;
    }
}
