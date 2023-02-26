<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\Segment\BaseDeg;
use Fhp\Syntax\Bin;

/**
 * Data Element Group: Verschlüsselungsalgorithmus (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section: D
 */
class VerschluesselungsalgorithmusV2 extends BaseDeg
{
    /** Allowed values: 2: Owner Symmetric (OSY) */
    public int $verwendungDesVerschluesselungsalgorithmus = 2; // The only possible value.
    /**
     * 2: Cipher Block Chaining (CBC)
     * 18: RSAES-PKCS#1 V1.5 (bei RAH, RDH)
     * 19: RSASSA-PSS (bei RAH, RDH)
     */
    public int $operationsmodus = 2; // CBC is what we need for PIN/TAN.
    /**
     * 13: 2-Key-Triple-DES (nicht zugelassen)
     * 14: AES-256 [AES]
     * The specification claims that value 13 is not allowed, but in practice and also in all the examples in the
     * specification, that's the value that is used.
     */
    public int $verschluesselungsalgorithmus = 13;
    /** Binary, max length: 512 */
    public Bin $wertDesAlgorithmusparametersSchluessel;
    /**
     * 5: Symmetrischer Schlüssel (nicht zugelassen) This is the recommended dummy value for PIN/TAN.
     * 6: Symmetrischer Schlüssel, verschlüsselt mit einem öffentlichen Schlüssel bei RAH und RDH (KYP).
     */
    public int $bezeichnerFuerAlgorithmusparameterSchluessel = 5; // Dummy for PIN/TAN
    /** Allowed values: 1: Initialization value, clear text (IVC) */
    public int $bezeichnerFuerAlgorithmusparameterIv = 1; // The only possible value.
    /** Max length: 512 Not allowed for PIN/TAN */
    public ?string $wertDesAlgorithmusparametersIv = null;

    public static function create(): VerschluesselungsalgorithmusV2
    {
        $result = new VerschluesselungsalgorithmusV2();
        // Note: The correct representation of the value that the specification recommends is "\0\0\0\0\0\0\0\0". But
        // that makes unit test failures unreadable because PhpUnit then interprets the entire surrounding message as
        // binary. Since the specification does not enforce its suggestion, we just something similar instead.
        $result->wertDesAlgorithmusparametersSchluessel = new Bin('00000000'); // Dummy for PIN/TAN
        return $result;
    }
}
