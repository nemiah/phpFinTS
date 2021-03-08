<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNHBK;

use Fhp\Segment\BaseDeg;

/**
 * Data ELement Group: Bezugsnachricht (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: D (letter B)
 */
class BezugsnachrichtV1 extends BaseDeg
{
    /** @var string References a previously sent {@link HNHBKv3::$dialogId} */
    public $dialogId;
    /** @var int References a previously sent {@link HNHBKv3::$nachrichtennummer} */
    public $nachrichtennummer;
}
