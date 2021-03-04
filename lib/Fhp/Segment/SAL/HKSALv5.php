<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SAL;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Saldenabfrage (Version 5)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: VII.2.2 a)
 */
class HKSALv5 extends BaseSegment implements Paginateable
{
    /** @var \Fhp\Segment\Common\KtvV3 */
    public $kontoverbindungAuftraggeber;
    /** @var bool */
    public $alleKonten;
    /** @var int|null */
    public $maximaleAnzahlEintraege;
    /** @var string|null Max length: 35 */
    public $aufsetzpunkt;

    public static function create(\Fhp\Segment\Common\KtvV3 $ktv, bool $alleKonten, ?string $aufsetzpunkt = null): HKSALv5
    {
        $result = HKSALv5::createEmpty();
        $result->kontoverbindungAuftraggeber = $ktv;
        $result->alleKonten = $alleKonten;
        $result->aufsetzpunkt = $aufsetzpunkt;
        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
