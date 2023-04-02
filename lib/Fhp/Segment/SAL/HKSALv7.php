<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SAL;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Paginateable;

/**
 * Segment: Saldenabfrage (Version 7)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.2.2 a)
 */
class HKSALv7 extends BaseSegment implements Paginateable
{
    public \Fhp\Segment\Common\Kti $kontoverbindungInternational;
    public bool $alleKonten;
    public ?int $maximaleAnzahlEintraege = null;
    /** Max length: 35 */
    public ?string $aufsetzpunkt = null;

    public static function create(\Fhp\Segment\Common\Kti $kti, bool $alleKonten, ?string $aufsetzpunkt = null): HKSALv7
    {
        $result = HKSALv7::createEmpty();
        $result->kontoverbindungInternational = $kti;
        $result->alleKonten = $alleKonten;
        $result->aufsetzpunkt = $aufsetzpunkt;
        return $result;
    }

    public function setPaginationToken(string $paginationToken)
    {
        $this->aufsetzpunkt = $paginationToken;
    }
}
