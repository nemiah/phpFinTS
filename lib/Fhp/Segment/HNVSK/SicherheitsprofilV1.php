<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\Model\TanMode;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Sicherheitsprofil (Version 1)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX0hCQ0lfUmVsXzIwMjQtMDYtMTFfZmluYWxfdmVyc2lvbi5wZGYiLCJwYWdlIjoxMjd9.HKqFIKBMLQVfvQfQFpgjJ9U93yv4mM3Now8IMdEIORY/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_2024-06-11_final_version.pdf
 * Section: D
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX1BJTlRBTl8yMDIwLTA3LTEwX2ZpbmFsX3ZlcnNpb24ucGRmIiwicGFnZSI6MTI3fQ.FJHEt1OwhZgDgfpwfO_ikZRn_hX8rbiSuesG2yyEle0/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
 * Section: B.9.1
 */
class SicherheitsprofilV1 extends BaseDeg
{
    public const VERSION_EIN_SCHRITT_VERFAHREN = 1;
    public const VERSION_ZWEI_SCHRITT_VERFAHREN = 2;

    /** Allowed values: "PIN", "RAH" */
    public string $sicherheitsverfahren;
    /** Allowed values: 1, 2 (for "PIN"), 7, 9, 10 (for "RAH") */
    public int $versionDesSicherheitsverfahrens;

    /**
     * @param TanMode|null $tanMode Optionally specifies which two-step TAN mode to use, defaults to 999 (single step).
     */
    public static function createPIN(?TanMode $tanMode): SicherheitsprofilV1
    {
        $result = new SicherheitsprofilV1();
        $result->sicherheitsverfahren = 'PIN';
        $result->versionDesSicherheitsverfahrens =
            $tanMode === null ? static::VERSION_EIN_SCHRITT_VERFAHREN : static::VERSION_ZWEI_SCHRITT_VERFAHREN;
        return $result;
    }
}
