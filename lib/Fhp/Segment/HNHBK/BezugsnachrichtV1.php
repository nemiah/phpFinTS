<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNHBK;

use Fhp\Segment\BaseDeg;

/**
 * Data ELement Group: Bezugsnachricht (Version 1)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: G (Data Dictionary) under letter B
 */
class BezugsnachrichtV1 extends BaseDeg
{
    /** References a previously sent {@link HNHBKv3::$dialogId} */
    public string $dialogId;
    /** References a previously sent {@link HNHBKv3::$nachrichtennummer} */
    public int $nachrichtennummer;
}
