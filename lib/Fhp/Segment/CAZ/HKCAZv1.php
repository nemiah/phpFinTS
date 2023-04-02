<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\CAZ;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Kontoumsätze/Zeitraum (camt)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.3.1.1.1
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
