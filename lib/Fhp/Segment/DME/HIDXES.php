<?php

namespace Fhp\Segment\DME;

use Fhp\Segment\SegmentInterface;

interface HIDXES extends SegmentInterface
{
    public function getParameter(): SEPADirectDebitMinimalLeadTimeProvider;
}
