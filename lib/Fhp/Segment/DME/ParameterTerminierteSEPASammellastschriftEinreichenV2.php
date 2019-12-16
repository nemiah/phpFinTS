<?php

namespace Fhp\Segment\DME;

use Fhp\Segment\BaseDeg;

class ParameterTerminierteSEPASammellastschriftEinreichenV2 extends BaseDeg
{
    /** @var string */
    public $minimaleVorlaufzeitCodiert;

    /** @var string */
    public $maximaleVorlaufzeitCodiert;

    /** @var int */
    public $maximaleAnzahlDirectDebitTransferTransactionInformation;

    /** @var bool */
    public $summenfeldBenoetigt;

    /** @var bool */
    public $einzelbuchungErlaubt;

    /** @var string[]|null @Max(4096) */
    public $zulaessigePurposecodes;

    /** @var string[]|null @Max(256) */
    public $unterstuetzteSEPADatenformate;

    public function getMinimaleVorlaufzeit(string $seqType, string $coreType = 'CORE')
    {
        $parsed = MinimaleVorlaufzeitSEPALastschrift::parseCoded($this->minimaleVorlaufzeitCodiert);
        return $parsed[$coreType][$seqType] ?? null;
    }
}
