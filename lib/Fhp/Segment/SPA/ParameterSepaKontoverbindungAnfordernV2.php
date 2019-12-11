<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\SPA;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Parameter SEPA-Kontoverbindung anfordern (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: D (letter P)
 */
class ParameterSepaKontoverbindungAnfordernV2 extends BaseDeg implements ParameterSepaKontoverbindungAnfordern
{
    /** @var bool */
    public $einzelkontenabrufErlaubt;
    /** @var bool */
    public $nationaleKontoverbindungErlaubt;
    /** @var bool */
    public $strukturierterVerwendungszweckErlaubt;
    /** @var bool */
    public $eingabeAnzahlEintraegeErlaubt;
    /** @var string[] @Max(99) Max length each: 256 */
    public $unterstuetzteSepaDatenformate;

    /** {@inheritdoc} */
    public function getUnterstuetzteSepaDatenformate()
    {
        return $this->unterstuetzteSepaDatenformate;
    }
}
