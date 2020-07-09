<?php /** @noinspection PhpUnused */

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
    /** @var \Fhp\Segment\Common\Kto */
    public $kontoverbindungAuftraggeber;
    /** @var bool */
    public $alleKonten;
    /** @var string|null */
    public $kontowaehrung;
    /** @var int|null Only allowed if HISALS $eingabeAnzahlEintraegeErlaubt says so. */
    public $maximaleAnzahlEintraege;
    /** @var string|null Max length: 35 */
    public $aufsetzpunkt;

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
