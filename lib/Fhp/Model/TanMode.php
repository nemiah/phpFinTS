<?php /** @noinspection PhpUnused */

namespace Fhp\Model;

/**
 * Interface TanMode
 *
 * For two-step authentication, users need to enter a TAN, which can be obtained in various ways (SMS, TAN generator
 * device, and so on). Users regularly have multiple ways to obtain a TAN even for a single bank, so they will need to
 * choose how they want to receive/generate the TAN. Each {@link TanMode} describes one of these options.
 *
 * @package Fhp\Model
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
     * @return integer The ID of this TanMode. This is what the application needs to persist when it wants to remember
     *     the users decision for future transactions.
     */
    public function getId();

    /**
     * @return string A user-readable name, e.g. for display in a list.
     */
    public function getName();

    /**
     * @return string A user-readable label for the text field that displays the challenge to the user.
     */
    public function getChallengeLabel();

    /**
     * @return integer The maximum length of the challenge. The application can use this to appropriately resize the
     *     text field that displays the challenge to the user.
     */
    public function getMaxChallengeLength();

    /**
     * @return integer The maximum length of TANs entered in this mode. The application can use this to restrict the TAN
     *     input field or to do client-side validation.
     */
    public function getMaxTanLength();

    /**
     * @return integer The allowed TAN format. See the FORMAT_* constants above. The application can use this to
     *     restrict the TAN input field or to do client-side validation.
     */
    public function getTanFormat();

    /**
     * @return boolean If true, there are potentially multiple TAN devices (e.g. multiple mobile phones) associated with
     *     this TanMode (e.g. if it's the smsTAN mode), and the user needs to pick the device *in addition to* and after
     *     picking this TanMode.
     */
    public function needsTanDevice();
}
