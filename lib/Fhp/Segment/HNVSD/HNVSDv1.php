<?php

namespace Fhp\Segment\HNVSD;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Verschlüsselte Daten (Version 1)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX0hCQ0lfUmVsXzIwMjQtMDYtMTFfZmluYWxfdmVyc2lvbi5wZGYiLCJwYWdlIjoxMjd9.HKqFIKBMLQVfvQfQFpgjJ9U93yv4mM3Now8IMdEIORY/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_2024-06-11_final_version.pdf
 * Section: B.5.4
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section B.8
 */
class HNVSDv1 extends BaseSegment
{
    /**
     * The specification demands that HNVSD always has segment number 998. See section B.8.
     */
    public const SEGMENT_NUMBER = 999;

    /**
     * Note: This field is called "encrypted", but for PIN/TAN it contains plaintext data anyway, because the encryption
     * is done in the transport layer (TLS).
     */
    public Bin $datenVerschluesselt;

    /**
     * @param BaseSegment[] $segments Some segments that will be serialized into the data field.
     */
    public static function create(array $segments): HNVSDv1
    {
        $result = HNVSDv1::createEmpty();
        $result->segmentkopf->segmentnummer = static::SEGMENT_NUMBER;
        $data = '';
        foreach ($segments as $segment) {
            $data .= $segment->serialize();
        }
        $result->datenVerschluesselt = new Bin($data);
        return $result;
    }
}
