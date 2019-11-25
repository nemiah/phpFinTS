<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HNSHA;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Benutzerdefinierte Signatur (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: D (letter B)
 */
class BenutzerdefinierteSignaturV1 extends BaseDeg
{
    /** @var string Max length: 99 */
    public $pin;
    /** @var string|null Max length: 99 */
    public $tan;

    /**
     * @param string $pin
     * @param string|null $tan
     * @return BenutzerdefinierteSignaturV1
     */
    public static function create($pin, $tan)
    {
        $result = new BenutzerdefinierteSignaturV1();
        $result->pin = $pin;
        $result->tan = $tan;
        return $result;
    }
}
