<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIRMS;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Rückmeldung (Version 1)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: G (Data Dictionary) under letter R
 */
class Rueckmeldung extends BaseDeg
{
    /** See also the Rueckmeldungscode class/enum. */
    public int $rueckmeldungscode;
    /**
     * O: bei Verwendung im Segment HIRMS
     * N: bei Verwendung im Segment HIRMG
     * Max length: 7
     */
    public ?string $bezugsdatenelement = null;
    /** Max length: 80 */
    public string $rueckmeldungstext;
    /** @var string[]|null @Max(10), max length each: 35 */
    public ?array $rueckmeldungsparameter = null;

    /**
     * This is not part of the FinTS wire format, but for convenience we store it here. If this Rueckmeldung pertains to
     * a particular segment of the request, then this will be its segment number.
     * @Ignore
     */
    public ?int $referenceSegment = null;

    public function __toString()
    {
        $referenceSegment = $this->referenceSegment === null ? 'global' : "wrt seg $this->referenceSegment";
        $result = "$this->rueckmeldungscode ($referenceSegment): $this->rueckmeldungstext";
        if ($this->bezugsdatenelement !== null) {
            $result .= " (wrt DE $this->bezugsdatenelement)";
        }
        if ($this->rueckmeldungsparameter !== null && count($this->rueckmeldungsparameter) > 0) {
            $result .= ' [' . implode(', ', $this->rueckmeldungsparameter) . ']';
        }
        return $result;
    }
}
