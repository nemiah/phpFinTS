<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseDeg;

class ParameterZweiSchrittTanEinreichungV1 extends BaseDeg implements ParameterZweiSchrittTanEinreichung
{
    /** @var bool */
    public $einschrittVerfahrenErlaubt;
    /** @var bool */
    public $mehrAlsEinTanPflichtigerAuftragProNachrichtErlaubt;
    /**
     * 0: Auftrags-Hashwert nicht unterstützt
     * 1: RIPEMD-160
     * 2: SHA-1
     * @var int
     */
    public $auftragsHashwertverfahren;
    /**
     * 0: Banken-Signatur von HITAN nicht erlaubt
     * 1: RDH-1 (wird in FinTS V3.0 nicht verwendet)
     * 2: RDH-2 (in FinTS V3.0)
     * @var int
     */
    public $sicherheitsprofilBankenSignatureBeiHitan;
    /** @var VerfahrensparameterZweiSchrittVerfahrenV1[] @Max(98) */
    public $verfahrensparameterZweiSchrittVerfahren;

    /** @return bool */
    public function getEinschrittVerfahrenErlaubt()
    {
        return $this->einschrittVerfahrenErlaubt;
    }

    /** @return VerfahrensparameterZweiSchrittVerfahrenV1[] */
    public function getVerfahrensparameterZweiSchrittVerfahren()
    {
        return $this->verfahrensparameterZweiSchrittVerfahren;
    }
}
