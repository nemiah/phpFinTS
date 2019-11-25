<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung ZV international (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.3.3
 */
class Ktz extends BaseDeg
{
    /** @var bool Whether it's a SEPA account that has IBAN/BIC, or not (e.g. a stock depot) */
    public $kontoverwendungSepa;
    /** @var string|null Max length: 34 */
    public $iban;
    /** @var string|null Max length: 11, required if IBAN is present. */
    public $bic;
    /** @var string Also known as Depotnummer. */
    public $kontonummer;
    /** @var string|null */
    public $unterkontomerkmal;
    /** @var Kik */
    public $kreditinstitutskennung;
}
