<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\SegmentInterface;

interface HIDXES extends SegmentInterface
{
    public function getParameter(): SEPADirectDebitMinimalLeadTimeProvider;

    public function createRequestSegment(): BaseSegment; // TODO Use more specific return type here?
}
