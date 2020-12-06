<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\SPA;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: SEPA-Kontoverbindung anfordern (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section C.10.1.4 a)
 */
class HKSPAv2 extends BaseSegment implements Paginateable
{
    /**
     * If left empty, all accounts will be returned.
     * @var \Fhp\Segment\Common\KtvV3[]|null @Max(999)
     */
    public $kontoverbindung;
    /** @var int|null Only allowed if {@link ParameterSepaKontoverbindungAnfordernV2::$eingabeAnzahlEintraegeErlaubt} says so. */
    public $maximaleAnzahlEintraege;
    /** @var string|null For pagination. */
    public $aufsetzpunkt;

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
