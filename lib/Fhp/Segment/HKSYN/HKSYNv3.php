<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\HKSYN;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Synchronisierung (Version 3)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: C.8.1.2
 */
class HKSYNv3 extends BaseSegment
{
    /**
     * 0: Neue Kundensystem-ID zurückmelden
     * 1: Letzte verarbeitete Nachrichtennummer zurückmelden
     * 2: Signatur-ID zurückmelden
     */
    public int $synchronisierungsmodus = 0; // The only mode we need in practice.
}
