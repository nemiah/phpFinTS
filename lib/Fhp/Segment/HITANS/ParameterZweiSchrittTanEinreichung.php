<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

interface ParameterZweiSchrittTanEinreichung
{
    /** @return bool */
    public function getEinschrittVerfahrenErlaubt();

    /** @return VerfahrensparameterZweiSchrittVerfahren[] */
    public function getVerfahrensparameterZweiSchrittVerfahren();
}
