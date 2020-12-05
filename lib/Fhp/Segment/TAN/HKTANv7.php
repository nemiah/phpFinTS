<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

/**
 * Segment: Geschäftsvorfall Zwei-Schritt-TAN-Einreichung (Version 7)
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
 * Section: B.5.2 a)
 */
class HKTANv7 extends HKTANv6
{
    // NOTE: While all fields remain the same as with HKTANv6, the $tanProzess field can now have the value 'S'.
    // If it does, $auftragsreferenz and $weitereTanFolgt are mandatory.
}
