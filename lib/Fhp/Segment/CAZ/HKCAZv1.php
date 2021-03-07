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
    /** @var \Fhp\Segment\Common\Kti */
    public $kontoverbindungInternational;

    /** @var UnterstuetzteCamtMessages */
    public $unterstuetzteCamtMessages;

    /** @var bool Only allowed if {@link ParameterKontoumsaetzeCamt::$alleKontenErlaubt} says so. */
    public $alleKonten;
    /** @var string|null JJJJMMTT gemäß ISO 8601 */
    public $vonDatum;
    /** @var string|null JJJJMMTT gemäß ISO 8601 */
    public $bisDatum;
    /** @var int|null Only allowed if {@link ParameterKontoumsaetzeCamt::$eingabeAnzahlEintraegeErlaubt} says so. */
    public $maximaleAnzahlEintraege;
    /** @var string|null Max length: 35 */
    public $aufsetzpunkt;

    public static function create(\Fhp\Segment\Common\Kti $kti, UnterstuetzteCamtMessages $unterstuetzteCamtMessages,
                                  bool $alleKonten, ?\DateTime $vonDatum, ?\DateTime $bisDatum, ?string $aufsetzpunkt = null): HKCAZv1
    {
        $result = HKCAZv1::createEmpty();
        $result->kontoverbindungInternational = $kti;
        $result->unterstuetzteCamtMessages = $unterstuetzteCamtMessages;
        $result->alleKonten = $alleKonten;
        $result->vonDatum = $vonDatum === null ? null : $vonDatum->format('Ymd');
        $result->bisDatum = $bisDatum === null ? null : $bisDatum->format('Ymd');
        $result->aufsetzpunkt = $aufsetzpunkt;

        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
