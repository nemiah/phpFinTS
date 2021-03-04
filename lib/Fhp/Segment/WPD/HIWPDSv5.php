<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\WPD;



use Fhp\Segment\BaseGeschaeftsvorfallparameter;
use Fhp\Segment\BaseGeschaeftsvorfallparameterOld;

/**
 * Segment: Saldenabfrage Parameter (Version 5)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: VII.2.2 c)
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
