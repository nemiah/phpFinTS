<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SAL;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Saldenabfrage (Version 4)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI21o.pdf
 * Section: VII.2.2 a)
 */
class HKSALv4 extends BaseSegment implements Paginateable
{
    public \Fhp\Segment\Common\Kto $kontoverbindungAuftraggeber;
    public bool $alleKonten;
    public ?string $kontowaehrung = null;
    public ?int $maximaleAnzahlEintraege = null;
    /** Max length: 35 */
    public ?string $aufsetzpunkt = null;

    public static function create(\Fhp\Segment\Common\Kto $kto, ?string $aufsetzpunkt = null): HKSALv4
    {
        $result = HKSALv4::createEmpty();
        $result->kontoverbindungAuftraggeber = $kto;
        $result->aufsetzpunkt = $aufsetzpunkt;
        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
