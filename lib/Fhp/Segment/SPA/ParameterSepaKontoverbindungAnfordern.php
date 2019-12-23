<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\SPA;

/**
 * Data Element Group: Parameter SEPA-Kontoverbindung anfordern
 */
interface ParameterSepaKontoverbindungAnfordern
{
    /** @return string[] */
    public function getUnterstuetzteSepaDatenformate(): array;
}
