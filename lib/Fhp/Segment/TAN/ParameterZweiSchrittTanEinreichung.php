<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Model\TanMode;

interface ParameterZweiSchrittTanEinreichung
{
    public function isEinschrittVerfahrenErlaubt(): bool;

    /** @return TanMode[] */
    public function getVerfahrensparameterZweiSchrittVerfahren(): array;
}
