<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment;

/**
 * This is a base format for segments with various names, each of which describes a potential business transaction that
 * the bank supports. The presence of the {@link BaseGeschaeftsvorfallparameter} instance in the BPD indicates that the
 * type of transaction is supported, and depending on the particular transaction, it may contain further parameters,
 * which are implemented in sub-classes of {@link BaseGeschaeftsvorfallparameter}. Note that the segment version of this
 * segment matches the version of the potential request segment that we could send to the server.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: D.6
 */
abstract class BaseGeschaeftsvorfallparameter extends BaseSegment
{
    /**
     * Maximum number of request segments of this kind that can be included in a single request message
     */
    public int $maximaleAnzahlAuftraege;
    /**
     * Minimum number of signatures required for this kind of business transaction. Note that zero signatures is
     * equivalent to an anonymous connection and one signature (the most common case) can be satisfied with PIN/TAN.
     */
    public int $anzahlSignaturenMindestens;
    /**
     * Minimum cryptographic security required for this transaction type, where 0 means none.
     */
    public int $sicherheitsklasse;

    // NOTE: Parameters specific to the respective transaction type follow here and are implemented in sub-classes.
}
