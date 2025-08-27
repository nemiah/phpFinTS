<?php
/** @noinspection PhpUnused */

// NOTE: In FinTsTestCase, this namespace name is hard-coded in order to be able to mock the time() function below.

namespace Fhp\Segment\HNVSK;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Sicherheitsdatum und -uhrzeit (Version 2)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX0hCQ0lfUmVsXzIwMjQtMDYtMTFfZmluYWxfdmVyc2lvbi5wZGYiLCJwYWdlIjoxMjd9.HKqFIKBMLQVfvQfQFpgjJ9U93yv4mM3Now8IMdEIORY/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_2024-06-11_final_version.pdf
 * Section: D
 */
class SicherheitsdatumUndUhrzeitV2 extends BaseDeg
{
    /**
     * 1: Sicherheitszeitstempel (STS)
     * 6: Certificate Revocation Time (CRT)
     */
    public int $datumUndZeitbezeichner = 1; // This library does not support recovation, so STS is all we need.
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $datum = null;
    /** hhmmss gemäß ISO 8601, local time (no time zone support). */
    public ?string $uhrzeit = null;

    /**
     * @return SicherheitsdatumUndUhrzeitV2 For the current time.
     */
    public static function now(): SicherheitsdatumUndUhrzeitV2
    {
        $result = new SicherheitsdatumUndUhrzeitV2();
        try {
            $now = new \DateTime('@' . time()); // Call unqualified time() for unit test mocking to work.
            $result->datum = $now->format('Ymd');
            $result->uhrzeit = $now->format('His');
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to get current date', 0, $e);
        }
        return $result;
    }
}
