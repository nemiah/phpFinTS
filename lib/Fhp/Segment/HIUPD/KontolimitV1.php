<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontolimit (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: V.3 "Kontoinformation" > Nr. 8
 */
class KontolimitV1 extends BaseDeg
{
    /** @var string Allowed values: E, T, W, M, Z */
    public $limitart;
    /** @var \Fhp\Segment\Common\Btg */
    public $limitbetrag;
    /** @var integer|null If present, must be greater than 0 */
    public $limitTage;
}
