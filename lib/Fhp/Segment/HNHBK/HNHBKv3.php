<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HNHBK;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Nachrichtenkopf (Version 3)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: B.5.2
 */
class HNHBKv3 extends BaseSegment
{
    const NACHRICHTENGROESSE_LENGTH = 12;

    /**
     * The length of the entire message (after encryption and compression) in bytes. While this is morally a number, the
     * specification requires padding it to 12 digits, so it is implemented as a string instead.
     * @var string
     */
    public $nachrichtengroesse = '000000000000'; // Ensure this field has always length 12.
    /**
     * Version 2.0.1 : 201 (Spezifikationsstatus: obsolet)
     * Version 2.1 : 210 (Spezifikationsstatus: obsolet)
     * Version 2.2 : 220 (Spezifikationsstatus: obsolet)
     * Version 3.0 : 300
     * @var int
     */
    public $hbciVersion = 300; // This library implements FinTS 3.0.
    /** @var string */
    public $dialogId;
    /** @var int Must be positive. */
    public $nachrichtennummer;
    /** @var BezugsnachrichtV1|null Never sent to server, but always present in responses. */
    public $bezugsnachricht;

    public function getNachrichtengroesse(): int
    {
        return intval($this->nachrichtengroesse);
    }

    /**
     * @param int $nachrichtengroesse Length of the entire message in bytes.
     */
    public function setNachrichtengroesse(int $nachrichtengroesse)
    {
        $this->nachrichtengroesse = str_pad($nachrichtengroesse, static::NACHRICHTENGROESSE_LENGTH, '0', STR_PAD_LEFT);
    }
}
