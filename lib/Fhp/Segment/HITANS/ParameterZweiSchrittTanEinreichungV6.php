<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseDeg;

class ParameterZweiSchrittTanEinreichungV6 extends BaseDeg implements ParameterZweiSchrittTanEinreichung
{
    /** @var boolean */
    public $einschrittVerfahrenErlaubt;
    /** @var boolean */
    public $mehrAlsEinTanPflichtigerAuftragProNachrichtErlaubt;
    /**
     * 0: Auftrags-Hashwert nicht unterstützt
     * 1: RIPEMD-160
     * 2: SHA-1
     * @var integer
     */
    public $auftragsHashwertverfahren;
    /** @var VerfahrensparameterZweiSchrittVerfahrenV6[] @Max(98) */
    public $verfahrensparameterZweiSchrittVerfahren;

    /** @return VerfahrensparameterZweiSchrittVerfahrenV6[] */
    public function getVerfahrensparameterZweiSchrittVerfahren()
    {
        return $this->verfahrensparameterZweiSchrittVerfahren;
    }
}
