<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\CAZ;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Kontoumsätze/Zeitraum (camt)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.2.3.1.1.1 a)
 */
class HKCAZv1 extends BaseSegment implements Paginateable
{
    public \Fhp\Segment\Common\Kti $kontoverbindungInternational;
    public UnterstuetzteCamtMessages $unterstuetzteCamtMessages;
    /** Only allowed if {@link ParameterKontoumsaetzeCamt::$alleKontenErlaubt} says so. */
    public bool $alleKonten;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $vonDatum = null;
    /** JJJJMMTT gemäß ISO 8601 */
    public ?string $bisDatum = null;
    /** Only allowed if {@link ParameterKontoumsaetzeCamt::$eingabeAnzahlEintraegeErlaubt} says so. */
    public ?int $maximaleAnzahlEintraege = null;
    /** Max length: 35 */
    public ?string $aufsetzpunkt = null;

    public static function create(\Fhp\Segment\Common\Kti $kti, UnterstuetzteCamtMessages $unterstuetzteCamtMessages,
        bool $alleKonten, ?\DateTime $vonDatum, ?\DateTime $bisDatum, ?string $aufsetzpunkt = null): HKCAZv1
    {
        $result = HKCAZv1::createEmpty();
        $result->kontoverbindungInternational = $kti;
        $result->unterstuetzteCamtMessages = $unterstuetzteCamtMessages;
        $result->alleKonten = $alleKonten;
        $result->vonDatum = $vonDatum?->format('Ymd');
        $result->bisDatum = $bisDatum?->format('Ymd');
        $result->aufsetzpunkt = $aufsetzpunkt;

        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
