<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\WPD;

use Fhp\Segment\BaseGeschaeftsvorfallparameterOld;

/**
 * Segment: Parameter Depotaufstellung
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: O - Parameter Depotaufstellung
 */
class HIWPDSv5 extends BaseGeschaeftsvorfallparameterOld implements HIWPDS
{
    /** @var ParameterDepotaufstellungV2 */
    public $parameter;

    public function getParameter(): ParameterDepotaufstellung
    {
        return $this->parameter;
    }
}
