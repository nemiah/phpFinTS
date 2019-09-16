<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseDeg;

/**
 * Class KtvV1
 *
 * This is not explicitly specified as "version 3", but before this one there was a version without $unterkontomerkmal
 * used in HBCI 2.0 and 2.1, whereas this is for HBCI 2.2.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: II.5.3.3 "Kontoverbindung"
 *
 * @package Fhp\Segment\HIUPD
 */
class KtvV3 extends BaseDeg
{
    /** @var string */
    public $kontonummer;
    /** @var string|null */
    public $unterkontomerkmal;
    /** @var integer */
    public $laenderkennzeichen;
    /** @var string|null */
    public $kreditinstitutionscode;
}
