<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Kontoumsätze anfordern/Zeitraum (Version 5)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: VII.2.1.1 a)
 */
class HKKAZv5 extends BaseSegment
{
    /** @var \Fhp\Segment\Common\KtvV3 */
    public $kontoverbindungAuftraggeber;
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
     * @param \Fhp\Segment\Common\KtvV3 $ktv
     * @param bool $alleKonten
     * @param \DateTime|null $vonDatum
     * @param \DateTime|null $bisDatum
     * @return HKKAZv5
     */
    public static function create($ktv, $alleKonten, $vonDatum, $bisDatum)
    {
        $result = HKKAZv5::createEmpty();
        $result->kontoverbindungAuftraggeber = $ktv;
        $result->alleKonten = $alleKonten;
        $result->vonDatum = isset($vonDatum) ? $vonDatum->format('Ymd') : null;
        $result->bisDatum = isset($bisDatum) ? $bisDatum->format('Ymd') : null;
        return $result;
    }
}
