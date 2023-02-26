<?php
/** @noinspection PhpUnused */

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
    public const SINGLE_STEP_ID = 999;

    /**
     * Only digits are allowed, i.e. [0-9]+
     */
    public const FORMAT_NUMERICAL = 1;
    /**
     * Digits and characters are allowed, i.e. any ISO 8859 characters (incl. [äöüß]) but not \r or \n.
     */
    public const FORMAT_ALPHANUMERICAL = 2;

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
     * @return bool True if this TAN mode can be used with Prozessvariante 2. Since that's the only mode currently
     *     implemented in this library, you likely want to filter out any TAN modes that return false here, though those
     *     are rare in practice anyway.
     */
    public function isProzessvariante2(): bool;

    /**
     * @return bool True if the TAN mode is a "decoupled" one, meaning that there are no actual TANs passed back to the
     *     server. Instead, the user just presses some kind of "confirm" button on a separate device and the client
     *     application resumes processing with the server without submitting a TAN.
     */
    public function isDecoupled(): bool;

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
     * @throws \RuntimeException If {@link TanMode::isDecoupled()} returns true.
     */
    public function getMaxTanLength(): int;

    /**
     * @return int The allowed TAN format. See the FORMAT_* constants above. The application can use this to
     *     restrict the TAN input field or to do client-side validation.
     * @throws \RuntimeException If {@link TanMode::isDecoupled()} returns true.
     */
    public function getTanFormat(): int;

    /**
     * @return bool If true, there are potentially multiple {@link TanMedium} choices (e.g. multiple mobile phones)
     *     associated with this TanMode (e.g. if it's the smsTAN mode), and the user needs to pick the medium in
     *     addition to and after picking this TanMode.
     */
    public function needsTanMedium(): bool;

    public function getSmsAbbuchungskontoErforderlich(): bool;

    public function getAuftraggeberkontoErforderlich(): bool;

    public function getChallengeKlasseErforderlich(): bool;

    public function getAntwortHhdUcErforderlich(): bool;

    /**
     * For decoupled TAN modes only.
     * @return int The maximum number of times that {@link FinTs::checkDecoupledSubmission()} may be called for a given
     *     {@link TanRequest}. The bank may treat exceeding this number like a wrong TAN input. 0 means infinity.
     */
    public function getMaxDecoupledChecks(): int;

    /**
     * For decoupled TAN modes only.
     * @return int The minimum number of seconds to wait beween receiving the {@link TanRequest} and the first call to
     *     {@link FinTs::checkDecoupledSubmission()}.
     * @throws \RuntimeException If {@link TanMode::isDecoupled()} returns false.
     */
    public function getFirstDecoupledCheckDelaySeconds(): int;

    /**
     * For decoupled TAN modes only.
     * @return int The minimum number of seconds to wait beween subsequent calls to
     *     {@link FinTs::checkDecoupledSubmission()}.
     * @throws \RuntimeException If {@link TanMode::isDecoupled()} returns false.
     */
    public function getPeriodicDecoupledCheckDelaySeconds(): int;

    /**
     * For decoupled TAN modes only.
     *
     * If this function returns true, the application, while waiting for the user to confirm on their secondary device,
     * may ask the user to indicate manually when they have done so (as opposed to relying solely on automated polling
     * if allowed by {@link TanMode::allowsAutomatedPolling()}, which may be only allowed at quite low frequencies
     * depending on the bank).
     *
     * Note: If for whatever reason your application does not implement automated polling at all, it's probably safe to
     * ignore the return value of this function and all the polling functions below and just let the user confirm
     * manually either way. The server won't know whether a call to {@link FinTs::checkDecoupledSubmission()} was
     * triggered by the user or by automation.
     * To be extra safe, the application can use {@link FinTs::firstPollingDelaySeconds()} when receiving the
     * {@link TanRequest} to calculate the earliest possible submission time, and upon manual confirmation
     * {@link sleep()} for the remaining time if necessary.
     *
     * @return bool Whether manual confirmations by the user are allowed.
     * @throws \RuntimeException If {@link TanMode::isDecoupled()} returns false.
     */
    public function allowsManualConfirmation(): bool;

    /**
     * For decoupled TAN modes only.
     *
     * If this function returns true, the application may poll the server periodically and automatically while waiting
     * for the user to confirm on their secondary device, subject to the delays below.
     * @return bool Whether automated polling is allowed.
     */
    public function allowsAutomatedPolling(): bool;

    /**
     * This function is for internal use by the library implementation.
     * @return HKTAN&BaseSegment A newly created segment.
     */
    public function createHKTAN(): HKTAN;
}
