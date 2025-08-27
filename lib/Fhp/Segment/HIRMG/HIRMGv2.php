<?php

namespace Fhp\Segment\HIRMG;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\HIRMS\FindRueckmeldungTrait;
use Fhp\Segment\HIRMS\RueckmeldungContainer;

/**
 * Segment: Rückmeldungen zur Gesamtnachricht (Version 2)
 * Sender: Kreditinstitut
 *
 * Contains response code(s) that pertain to the request message as a whole (as opposed to individual segments, see also
 * HIRMS).
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section B.7.2
 */
class HIRMGv2 extends BaseSegment implements RueckmeldungContainer
{
    use FindRueckmeldungTrait; // For RueckmeldungContainer.

    /** @var \Fhp\Segment\HIRMS\Rueckmeldung[] @Max(99) */
    public array $rueckmeldung;
}
