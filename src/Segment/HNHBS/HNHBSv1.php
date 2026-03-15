<?php

namespace Fhp\Segment\HNHBS;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Nachrichtenabschluss (Version 1)
 */
class HNHBSv1 extends BaseSegment
{
    /** Must match the {@link HNHBKv2::$nachrichtennummer} in the same message. */
    public int $nachrichtennummer;
}
