<?php

/*
 * Author: Peter Eberhard, Copyright 2022 Launix Inh. Carl-Philip Hänsch
 */

namespace Fhp\Segment\CCM;

use Fhp\Segment\BaseDeg;

class ParameterSEPASammelueberweisungV1 extends BaseDeg
{
    /** @var int */
    public $maximaleAnzahlCrediTransferTransactionInformation;

    /** @var bool */
    public $summenfeldBenoetigt;

    /** @var bool */
    public $einzelbuchungErlaubt;
}
