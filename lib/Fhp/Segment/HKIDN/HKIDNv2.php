<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HKIDN;

use Fhp\Options\Credentials;
use Fhp\Segment\BaseSegment;

/**
 * Segment: Identifikation (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: C.3.1.2
 */
class HKIDNv2 extends BaseSegment
{
    /**
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
     * Section C.5
     */
    const ANONYMOUS_KUNDEN_ID = '9999999999';
    const MISSING_KUNDENSYSTEM_ID = '0';

    /** @var \Fhp\Segment\Common\Kik */
    public $kreditinstitutskennung;
    /** @var string Max length: 30 */
    public $kundenId;
    /** @var string Max length: 30 */
    public $kundensystemId;
    /**
     * 0: Kundensystem-ID wird nicht benötigt (HBCI DDV-Verfahren und chipkartenbasierte Verfahren ab
     *    Sicherheitsprofil-Version 3)
     * 1: Kundensystem-ID wird benötigt (sonstige HBCI RAH-/RDH- und PIN/TAN-Verfahren)
     * @var int
     */
    public $kundensystemStatus = 1; // This library only supports PIN/TAN, hence 1 is the right choice.

    public static function create(string $kreditinstitutionscode, Credentials $credentials, string $kundensystemId): HKIDNv2
    {
        $result = HKIDNv2::createEmpty();
        $result->kreditinstitutskennung = \Fhp\Segment\Common\Kik::create($kreditinstitutionscode);
        $result->kundenId = $credentials->benutzerkennung;
        $result->kundensystemId = $kundensystemId;
        $result->kundensystemStatus = 1; // This library only supports PIN/TAN, hence 1 is the right choice.
        return $result;
    }

    public static function createAnonymous(string $kreditinstitutionscode): HKIDNv2
    {
        $result = HKIDNv2::createEmpty();
        $result->kreditinstitutskennung = \Fhp\Segment\Common\Kik::create($kreditinstitutionscode);
        $result->kundenId = static::ANONYMOUS_KUNDEN_ID;
        $result->kundensystemId = static::MISSING_KUNDENSYSTEM_ID;
        $result->kundensystemStatus = 0; // Prescribed value for anonymous access.
        return $result;
    }
}
