<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\Model\TanMode;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Sicherheitsprofil (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section: D
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: B.9.1
 */
class SicherheitsprofilV1 extends BaseDeg
{
    public const VERSION_EIN_SCHRITT_VERFAHREN = 1;
    public const VERSION_ZWEI_SCHRITT_VERFAHREN = 2;

    /** Allowed values: "PIN", "RAH" */
    public string $sicherheitsverfahren;
    /** Allowed values: 1, 2 (for "PIN"), 7, 9, 10 (for "RAH") */
    public int $versionDesSicherheitsverfahrens;

    /**
     * @param TanMode|null $tanMode Optionally specifies which two-step TAN mode to use, defaults to 999 (single step).
     */
    public static function createPIN(?TanMode $tanMode): SicherheitsprofilV1
    {
        $result = new SicherheitsprofilV1();
        $result->sicherheitsverfahren = 'PIN';
        $result->versionDesSicherheitsverfahrens =
            $tanMode === null ? static::VERSION_EIN_SCHRITT_VERFAHREN : static::VERSION_ZWEI_SCHRITT_VERFAHREN;
        return $result;
    }
}
