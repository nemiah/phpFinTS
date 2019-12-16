<?php

namespace Fhp\Segment\DME;

use Fhp\Segment\BaseDeg;

class ParameterTerminierteSEPASammellastschriftEinreichenV1 extends BaseDeg
{
    /** @var int Must be => 1 */
    public $minimaleVorlaufzeitFNALRCUR;

    /** @var int */
    public $maximaleVorlaufzeitFNALRCUR;

    /** @var int Must be => 1 */
    public $minimaleVorlaufzeitFRSTOOFF;

    /** @var int */
    public $maximaleVorlaufzeitFRSTOOFF;

    /** @var int */
    public $maximaleAnzahlDirectDebitTransferTransactionInformation;

    /** @var bool */
    public $summenfeldBenoetigt;

    /** @var bool */
    public $einzelbuchungErlaubt;
}
