<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Kontoumsätze anfordern/Zeitraum (Version 7)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.1.1.2
 */
class HKKAZv7 extends BaseSegment
{
    /** @var \Fhp\Segment\Common\Kti */
    public $kontoverbindungInternational;
    /** @var bool Only allowed if HIKAZS $alleKontenErlaubt says so. */
    public $alleKonten;
    /** @var string|null JJJJMMTT gemäß ISO 8601 */
    public $vonDatum;
    /** @var string|null JJJJMMTT gemäß ISO 8601 */
    public $bisDatum;
    /** @var int|null Only allowed if HIKAZS $eingabeAnzahlEintraegeErlaubt says so. */
    public $maximaleAnzahlEintraege;
    /** @var string|null Max length: 35 */
    public $aufsetzpunkt;

    /**
     * @param \Fhp\Segment\Common\Kti $kti
     * @param bool $alleKonten
     * @param \DateTime|null $vonDatum
     * @param \DateTime|null $bisDatum
     * @return HKKAZv7
     */
    public static function create($kti, $alleKonten, $vonDatum, $bisDatum)
    {
        $result = HKKAZv7::createEmpty();
        $result->kontoverbindungInternational = $kti;
        $result->alleKonten = $alleKonten;
        $result->vonDatum = isset($vonDatum) ? $vonDatum->format('Ymd') : null;
        $result->bisDatum = isset($bisDatum) ? $bisDatum->format('Ymd') : null;
        return $result;
    }
}
