<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIBPA;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Unterstützte Sprachen (Version 2)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: G (Data Dictionary) under letter U
 */
class UnterstuetzteSprachenV2 extends BaseDeg
{
    /**
     * 0: Standard
     * 1: Deutsch, Code ‚de’ (German), Subset Deutsch, Codeset 1 (Latin 1)
     * 2: Englisch, Code ‚en’ (English), Subset Englisch, Codeset 1 (Latin 1)
     * 3: Französisch, Code ‚fr’ (French), Subset Französisch, Codeset 1 (Latin 1)
     * @var int[] @Max(9)
     */
    public array $unterstuetzteSprache;
}
