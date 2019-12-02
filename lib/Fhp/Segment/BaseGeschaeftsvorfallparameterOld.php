<?php /** @noinspection PhpUnused */

namespace Fhp\Segment;

/**
 * This is an older version of {@link BaseGeschaeftsvorfallparameter} (see there for documentation) used in FinTS 2.2
 * and older, so any HIxyzS segments specified back use this.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: IV.6 "Geschäftsvorfallparameter"
 */
abstract class BaseGeschaeftsvorfallparameterOld extends BaseSegment
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

    // NOTE: There is no Sicherheitsklasse here.

    // NOTE: Parameters specific to the respective transaction type follow here and are implemented in sub-classes.
}
