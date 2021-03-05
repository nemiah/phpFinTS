<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\WPD;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Depotaufstellung anfordern (Version 5)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.4.3.1
 */
class HKWPDv5 extends BaseSegment implements Paginateable
{
    /** @var \Fhp\Segment\Common\KtvV3 */
    public $kontoverbindungAuftraggeber;
    
    /** @var int|null Only allowed if {@link ParameterKontoumsaetzeV2::$eingabeAnzahlEintraegeErlaubt} says so. */
    public $maximaleAnzahlEintraege;


    public static function create(\Fhp\Segment\Common\KtvV3 $ktv): HKWPDv5
    {
        $result = HKWPDv5::createEmpty();
        $result->kontoverbindungAuftraggeber = $ktv;
        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
