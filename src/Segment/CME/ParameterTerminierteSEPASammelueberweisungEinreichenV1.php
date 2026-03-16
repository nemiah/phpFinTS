<?php

namespace Fhp\Segment\CME;

use Fhp\Segment\BaseDeg;

class ParameterTerminierteSEPASammelueberweisungEinreichenV1 extends BaseDeg
{
    public int $minimaleVorlaufzeit;
    public int $maximaleVorlaufzeit;
    public int $maximaleAnzahlCreditTransferTransactionInformation;
    public bool $summenfeldBenoetigt;
    public bool $einzelbuchungErlaubt;
}
