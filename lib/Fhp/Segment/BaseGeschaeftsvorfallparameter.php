<?php /** @noinspection PhpUnused */


namespace Fhp\Segment;

/**
 * Class BaseGeschaeftsvorfallparameter
 *
 * This is a base format for segments with various names, each of which describes a potential business transaction that
 * the bank supports. The presence of the {@link BaseGeschaeftsvorfallparameter} instance in the BPD indicates that the
 * type of transaction is supported, and depending on the particular transaction, it may contain further parameters,
 * which are implemented in sub-classes of {@link BaseGeschaeftsvorfallparameter}. Note that the segment version of this
 * segment matches the version of the potential request segment that we could send to the server.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: D.6
 *
 * @package Fhp\Segment
 */
class BaseGeschaeftsvorfallparameter extends BaseSegment
{
    /**
     * Maximum number of request segments of this kind that can be included in a single request message
     * @var integer
     */
    public $maximaleAnzahlAuftraege;
    /**
     * Minimum number of signatures required for this kind of business transaction. Note that zero signatures is
     * equivalent to an anonymous connection and one signature (the most common case) can be satisfied with PIN/TAN.
     * @var integer
     */
    public $anzahlSignaturenMindestens;
    /**
     * Minimum cryptographic security required for this transaction type, where 0 means none.
     * @var integer
     */
    public $sicherheitsklasse;

    // NOTE: Parameters specific to the respective transaction type follow here and are implemented in sub-classes.
}
