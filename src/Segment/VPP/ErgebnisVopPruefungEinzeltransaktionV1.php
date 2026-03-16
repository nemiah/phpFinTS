<?php

namespace Fhp\Segment\VPP;

use Fhp\Segment\BaseDeg;

class ErgebnisVopPruefungEinzeltransaktionV1 extends BaseDeg
{
    public string $ibanEmpfaenger;

    public ?string $ibanZusatzinformationen = null;

    public ?string $abweichenderEmpfaengername = null;

    public ?string $anderesIdentifikationmerkmal = null;

    /** Allowed values: RVMC, RCVC, RVNM, RVNA, PDNG */
    public string $vopPruefergebnis;

    public ?string $grundRVNA = null;
}
