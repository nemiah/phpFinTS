<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseSegment;

/**
 * Class HITANSv1
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter (Version 1)
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/FinTS_V3.0_2017-10-06-FV_RM.zip
 *
 * @package Fhp\Segment\HITANS
 */
class HITANSv1 extends BaseSegment
{
    /** @var integer */
    public $maximaleAnzahlAuftraege;
    /** @var integer Allowed values: 0, 1, 2, 3 */
    public $anzahlSignaturenMindestens;
    /** @var integer Allowed values: 0, 1, 2, 3, 4 */
    public $sicherheitsklasse;
    /** @var ParameterZweiSchrittTanEinreichungV1 */
    public $parameterZweiSchrittTanEinreichung;
}
