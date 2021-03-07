<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPA;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Userparameter allgemein (Version 4)
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 * Contains the main Userparameterdaten (UPD) data.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: E.2
 */
class HIUPAv4 extends BaseSegment
{
    /** @var string */
    public $benutzerkennung;
    /**
     * Note: The bank may send UPD version 0, which means these UPD are the most recent but should not be persisted.
     * @var int
     */
    public $updVersion;
    /**
     * 0: If the bank does not explicitly declare a business transaction type (i.e. request segment type) as supported,
     *    it does not support it, so sending such a request to the bank will always lead to failure.
     * 1: Explicitly declared types are definitely supported, anything else may be reported and can be sent; the bank
     *    will check online and accept/reject accordingly.
     * @var int
     */
    public $updVerwendung;
    /** @var string|null Max length: 35 */
    public $benutzername;
    /** @var string|null Max length: 2048 */
    public $erweiterungAllgemein;
}
