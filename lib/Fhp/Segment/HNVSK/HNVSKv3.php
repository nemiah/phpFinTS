<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\Model\TanMode;
use Fhp\Options\Credentials;
use Fhp\Options\FinTsOptions;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Kik;

/**
 * Segment: Verschlüsselungskopf (Version 3)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section: B.5.3
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section B.1
 * Section B.9.8
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section B.8
 */
class HNVSKv3 extends BaseSegment
{
    /**
     * The specification demands that HNVSK always has segment number 998. See section B.8.
     */
    public const SEGMENT_NUMBER = 998;

    /** @var SicherheitsprofilV1 */
    public $sicherheitsprofil;
    /**
     * For the PIN/TAN profile, this must be 998 (see section B.9.8).
     * @var int
     */
    public $sicherheitsfunktion = 998;
    /**
     * 1: Der Unterzeichner ist Herausgeber der signierten Nachricht, z. B. Erfasser oder Erstsignatur (ISS)
     * (Not allowed: 3: Der Unterzeichner unterstützt den Inhalt der Nachricht, z. B. bei Zweitsignatur (CON))
     * 4: Der Unterzeichner ist Zeuge, aber für den Inhalt der Nachricht nicht verantwortlich, z. B. Übermittler,
     *    welcher nicht Erfasser ist (WIT)
     * @var int
     */
    public $rolleDesSicherheitslieferanten = 1;
    /** @var SicherheitsidentifikationDetailsV2 */
    public $sicherheitsidentifikationDetails;
    /** @var SicherheitsdatumUndUhrzeitV2 */
    public $sicherheitsdatumUndUhrzeit;
    /** @var VerschluesselungsalgorithmusV2 */
    public $verschluesselungsalgorithmus;
    /** @var SchluesselnameV3 */
    public $schluesselname;
    /**
     * 0: Keine Kompression (NULL)
     * 1: Lempel, Ziv, Welch (LZW)
     * 2: Optimized LZW (COM)
     * 3: Lempel, Ziv (LZSS)
     * 4: LZ + Huffman Coding (LZHuf)
     * 5: PKZIP (ZIP)
     * 6: deflate (GZIP) (http://www.gzip.org/zlib)
     * 7: bzip2 (http://sourceware.cygnus.com/bzip2/)
     * 999: Gegenseitig vereinbart (ZZZ)
     * @var int
     */
    public $komprimierungsfunktion = 0; // This library does not support compression.
    /** @var ZertifikatV2|null For the PIN/TAN profile, this must be empty (see section B.9.8). */
    public $zertifikat;

    /**
     * @param FinTsOptions $options See {@link FinTsOptions}.
     * @param Credentials $credentials See {@link Credentials}.
     * @param string $kundensystemId See {@link SicherheitsidentifikationDetailsV2::$identifizierungDerPartei}.
     * @param TanMode|null $tanMode Optionally specifies which two-step TAN mode to use, defaults to 999 (single step).
     */
    public static function create(FinTsOptions $options, Credentials $credentials, string $kundensystemId, ?TanMode $tanMode): HNVSKv3
    {
        $result = HNVSKv3::createEmpty();
        $result->segmentkopf->segmentnummer = static::SEGMENT_NUMBER;
        $result->sicherheitsprofil = SicherheitsprofilV1::createPIN($tanMode);
        $result->sicherheitsidentifikationDetails = SicherheitsidentifikationDetailsV2::createForSender($kundensystemId);
        $result->sicherheitsdatumUndUhrzeit = SicherheitsdatumUndUhrzeitV2::now();
        $result->verschluesselungsalgorithmus = VerschluesselungsalgorithmusV2::create();
        $result->schluesselname = SchluesselnameV3::create(
            Kik::create($options->bankCode),
            $credentials->getBenutzerkennung(),
            SchluesselnameV3::CHIFFRIERSCHLUESSEL);
        return $result;
    }
}
