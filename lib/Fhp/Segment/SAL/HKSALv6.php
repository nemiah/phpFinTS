<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\SAL;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\PaginateableInterface;
use Fhp\Segment\PaginateableTrait;

/**
 * Segment: Saldenabfrage (Version 6)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.2.1 a)
 */
class HKSALv6 extends BaseSegment implements PaginateableInterface
{
    use PaginateableTrait;

    /** @var \Fhp\Segment\Common\KtvV3 */
    public $kontoverbindungAuftraggeber;
    /** @var bool */
    public $alleKonten;
    /** @var int|null */
    public $maximaleAnzahlEintraege;
    /** @var string|null Max length: 35 */
    public $aufsetzpunkt;

    public static function create(\Fhp\Segment\Common\KtvV3 $ktv, bool $alleKonten, ?string $aufsetzpunkt = null): HKSALv6
    {
        $result = HKSALv6::createEmpty();
        $result->kontoverbindungAuftraggeber = $ktv;
        $result->alleKonten = $alleKonten;
        $result->aufsetzpunkt = $aufsetzpunkt;
        return $result;
    }
}
