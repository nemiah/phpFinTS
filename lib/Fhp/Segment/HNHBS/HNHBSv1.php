<?php

namespace Fhp\Segment\HNHBS;

use Fhp\Segment\BaseSegment;

/**
 * Class HNHBSv1
 * Segment: Nachrichtenabschluss (Version 1)
 *
 * @package Fhp\Segment\HNHBS
 */
class HNHBSv1 extends BaseSegment
{
    /** @var integer Must match the {@link HNHBKv2#nachrichtennummer} in the same message. */
    public $nachrichtennummer;
}
