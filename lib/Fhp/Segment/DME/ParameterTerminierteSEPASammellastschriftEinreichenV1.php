<?php

namespace Fhp\Segment\DME;

use Fhp\Segment\DSE\ParameterTerminierteSEPAEinzellastschriftEinreichenV1;

class ParameterTerminierteSEPASammellastschriftEinreichenV1 extends ParameterTerminierteSEPAEinzellastschriftEinreichenV1
{
    /** @var int */
    public $maximaleAnzahlDirectDebitTransferTransactionInformation;

    /** @var bool */
    public $summenfeldBenoetigt;

    /** @var bool */
    public $einzelbuchungErlaubt;
}
