<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Segment\BaseDeg;

class ParameterZweiSchrittTanEinreichungV6 extends BaseDeg implements ParameterZweiSchrittTanEinreichung
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
    /** @var VerfahrensparameterZweiSchrittVerfahrenV6[] @Max(98) */
    public $verfahrensparameterZweiSchrittVerfahren;

    public function isEinschrittVerfahrenErlaubt(): bool
    {
        return $this->einschrittVerfahrenErlaubt;
    }

    public function getVerfahrensparameterZweiSchrittVerfahren(): array
    {
        return $this->verfahrensparameterZweiSchrittVerfahren;
    }
}
