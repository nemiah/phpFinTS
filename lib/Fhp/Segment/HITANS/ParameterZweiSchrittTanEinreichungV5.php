<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseDeg;

class ParameterZweiSchrittTanEinreichungV5 extends BaseDeg implements ParameterZweiSchrittTanEinreichung
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
    /** @var VerfahrensparameterZweiSchrittVerfahrenV5[] @Max(98) */
    public $verfahrensparameterZweiSchrittVerfahren;

    /** @return bool */
    public function getEinschrittVerfahrenErlaubt(): bool
    {
        return $this->einschrittVerfahrenErlaubt;
    }

    /** @return VerfahrensparameterZweiSchrittVerfahrenV5[] */
    public function getVerfahrensparameterZweiSchrittVerfahren(): array
    {
        return $this->verfahrensparameterZweiSchrittVerfahren;
    }
}
