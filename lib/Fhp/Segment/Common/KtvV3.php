<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Class KtvV3
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.3.1
 *
 * @package Fhp\Segment\Common
 */
class KtvV3 extends BaseDeg
{
    /** @var string */
    public $kontonummer;
    /** @var string|null */
    public $unterkontomerkmal;
    /** @var Kik */
    public $kik;
}
