<?php

namespace Fhp\Segment\VPP;

class ErgebnisVopPruefungEinzeltransaktion
{
    public string $ibanEmpfaenger;

    public ?string $ibanZusatzinformationen = null;

    public ?string $abweichenderEmpfängername = null;

    public ?string $anderesIdentifikationmerkmal = null;

    /** Allowed values: RVMC, RCVC, RVNM, RVNA, PDNG */
    public string $vopPruefergebnis;

    public ?string $grundRVNA = null;
}