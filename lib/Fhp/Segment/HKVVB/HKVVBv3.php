<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HKVVB;

use Fhp\Options\FinTsOptions;
use Fhp\Protocol\BPD;
use Fhp\Protocol\UPD;
use Fhp\Segment\BaseSegment;

/**
 * Segment: Verarbeitungsvorbereitung (Version 3)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: C.3.1.3
 */
class HKVVBv3 extends BaseSegment
{
    public int $bpdVersion = 0; // 0 means no BPD stored at client-side yet.
    public int $updVersion = 0; // 0 means no UPD stored at client-side yet.
    /**
     * 0: Standard
     * 1: Deutsch, Code ‚de’ (German), Subset Deutsch, Codeset 1 (Latin 1)
     * 2: Englisch, Code ‚en’ (English), Subset Englisch, Codeset 1 (Latin 1)
     * 3: Französisch, Code ‚fr’ (French), Subset Französisch, Codeset 1 (Latin 1)
     */
    public int $dialogsprache = 0; // The bank's default is fine.
    /** Max length: 25 */
    public string $produktbezeichnung;
    /** Max length: 5 */
    public string $produktversion;

    public static function create(FinTsOptions $options, ?BPD $bpd, ?UPD $upd): HKVVBv3
    {
        $result = HKVVBv3::createEmpty();
        $result->bpdVersion = $bpd === null ? 0 : $bpd->getVersion();
        $result->updVersion = $upd === null ? 0 : $upd->getVersion();
        $result->produktbezeichnung = $options->productName;
        $result->produktversion = $options->productVersion;
        return $result;
    }
}
