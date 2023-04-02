<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SPA;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Parameter SEPA-Kontoverbindung anfordern (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: D (letter P)
 */
class ParameterSepaKontoverbindungAnfordernV1 extends BaseDeg implements ParameterSepaKontoverbindungAnfordern
{
    use GetUnterstuetzteSepaDatenformateTrait;

    public bool $einzelkontenabrufErlaubt;
    public bool $nationaleKontoverbindungErlaubt;
    public bool $strukturierterVerwendungszweckErlaubt;
    /** @var string[] @Max(99) Max length each: 256 */
    public array $unterstuetzteSepaDatenformate;
}
