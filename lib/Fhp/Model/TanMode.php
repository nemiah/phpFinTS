<?php /** @noinspection PhpUnused */

namespace Fhp\Model;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\TAN\HKTAN;

/**
 * For two-step authentication, users need to enter a TAN, which can be obtained in various ways (SMS, TAN generator
 * device, and so on). Users regularly have multiple ways to obtain a TAN even for a single bank, so they will need to
 * choose how they want to receive/generate the TAN. Each {@link TanMode} describes one of these options.
 */
interface TanMode
{
    /**
     * The dummy ID for the single-step TAN mode.
     */
    const SINGLE_STEP_ID = 999;

    /**
     * Only digits are allowed, i.e. [0-9]+
     */
    const FORMAT_NUMERICAL = 1;
    /**
     * Digits and characters are allowed, i.e. any ISO 8859 characters (incl. [äöüß]) but not \r or \n.
     */
    const FORMAT_ALPHANUMERICAL = 2;

    /**
     * @return int The ID of this TanMode. This is what the application needs to persist when it wants to remember
     *     the users decision for future transactions.
     */
    public function getId(): int;

    /**
     * @return string A user-readable name, e.g. for display in a list.
     */
    public function getName(): string;

    /**
     * @return string A user-readable label for the text field that displays the challenge to the user.
     */
    public function getChallengeLabel(): string;

    /**
     * @return int The maximum length of the challenge. The application can use this to appropriately resize the
     *     text field that displays the challenge to the user.
     */
    public function getMaxChallengeLength(): int;

    /**
     * @return int The maximum length of TANs entered in this mode. The application can use this to restrict the TAN
     *     input field or to do client-side validation.
     */
    public function getMaxTanLength(): int;

    /**
     * @return int The allowed TAN format. See the FORMAT_* constants above. The application can use this to
     *     restrict the TAN input field or to do client-side validation.
     */
    public function getTanFormat(): int;

    /**
     * @return bool If true, there are potentially multiple {@link TanMedium} choices (e.g. multiple mobile phones)
     *     associated with this TanMode (e.g. if it's the smsTAN mode), and the user needs to pick the medium in
     *     addition to and after picking this TanMode.
     */
    public function needsTanMedium(): bool;

    /** @return bool */
    public function getSmsAbbuchungskontoErforderlich(): bool;

    /** @return bool */
    public function getAuftraggeberkontoErforderlich(): bool;

    /** @return bool */
    public function getChallengeKlasseErforderlich(): bool;

    /** @return bool */
    public function getAntwortHhdUcErforderlich(): bool;

    /**
     * This function is for internal use by the library implementation.
     * @return HKTAN&BaseSegment A newly created segment.
     */
    public function createHKTAN(): HKTAN;
}
