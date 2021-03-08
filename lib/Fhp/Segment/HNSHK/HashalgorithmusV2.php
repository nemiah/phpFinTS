<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNSHK;

use Fhp\Segment\BaseDeg;
use Fhp\Syntax\Bin;

/**
 * Data Element Group: Hashalgorithmus (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section D (letter H)
 */
class HashalgorithmusV2 extends BaseDeg
{
    /**
     * 1: Owner Hashing (OHA)
     * @var int
     */
    public $verwendungDesHashalgorithmus = 1; // The only allowed value.
    /**
     * 1: SHA-1 (nicht zugelassen)
     * 2: belegt
     * 3: SHA-256
     * 4: SHA-384
     * 5: SHA-512
     * 6: SHA-256 / SHA-256 (this means hashing twice, once in signature card and once in software)
     * 999: Gegenseitig vereinbart (ZZZ); (nicht zugelassen)
     * @var int
     */
    public $hashalgorithmus = 999; // The field is not used in PIN/TAN, so we put a dummy value.
    /**
     * 1: IVC (Initialization value, clear text)
     * @var int
     */
    public $bezeichnerFuerHashalgorithmusparameter = 1; // The only allowed value.
    /** @var Bin|null Binary, max length: 512; not allowed for PIN/TAN */
    public $wertDesHashalgorithmusparameters;
}
