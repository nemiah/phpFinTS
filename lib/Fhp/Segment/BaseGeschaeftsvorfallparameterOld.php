<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment;

/**
 * This is an older version of {@link BaseGeschaeftsvorfallparameter} (see there for documentation) used in FinTS 2.2
 * and older, so this is used by any HIxyzS segments specified back then.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: IV.6 "Geschäftsvorfallparameter"
 */
abstract class BaseGeschaeftsvorfallparameterOld extends BaseSegment
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

    // NOTE: There is no Sicherheitsklasse here.

    // NOTE: Parameters specific to the respective transaction type follow here and are implemented in sub-classes.
}
