<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Zertifikat (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section: D
 */
class ZertifikatV2 extends BaseDeg
{
    /** @var int Allowed values: 1, 2, 3 */
    public $zertifikatstyp;
    /** @var string Binary, max length 4096 */
    public $zertifikatsinhalt;
}
