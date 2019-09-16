<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseDeg;

class ParameterZweiSchrittTanEinreichungV1 extends BaseDeg
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
    /**
     * 0: Banken-Signatur von HITAN nicht erlaubt
     * 1: RDH-1 (wird in FinTS V3.0 nicht verwendet)
     * 2: RDH-2 (in FinTS V3.0)
     * @var integer
     */
    public $sicherheitsprofilBankenSignatureBeiHitan;
    /** @var VerfahrensparameterZweiSchrittVerfahrenV1[] @Max(98) */
    public $verfahrensparameterZweiSchrittVerfahren;
}
