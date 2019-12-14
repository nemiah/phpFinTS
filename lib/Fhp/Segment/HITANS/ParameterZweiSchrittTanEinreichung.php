<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

interface ParameterZweiSchrittTanEinreichung
{
    /** @return bool */
    public function getEinschrittVerfahrenErlaubt(): bool;

    /** @return VerfahrensparameterZweiSchrittVerfahren[] */
    public function getVerfahrensparameterZweiSchrittVerfahren(): array;
}
