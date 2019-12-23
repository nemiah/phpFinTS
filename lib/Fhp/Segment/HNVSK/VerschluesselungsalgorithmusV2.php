<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HNVSK;

use Fhp\DataTypes\Bin;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Verschlüsselungsalgorithmus (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20181129_final_version.pdf
 * Section: D
 */
class VerschluesselungsalgorithmusV2 extends BaseDeg
{
    /** @var int Allowed values: 2: Owner Symmetric (OSY) */
    public $verwendungDesVerschluesselungsalgorithmus = 2; // The only possible value.
    /**
     * 2: Cipher Block Chaining (CBC)
     * 18: RSAES-PKCS#1 V1.5 (bei RAH, RDH)
     * 19: RSASSA-PSS (bei RAH, RDH)
     * @var int
     */
    public $operationsmodus = 2; // CBC is what we need for PIN/TAN.
    /**
     * 13: 2-Key-Triple-DES (nicht zugelassen)
     * 14: AES-256 [AES]
     * The specification claims that value 13 is not allowed, but in practice and also in all the examples in the
     * specification, that's the value that is used.
     * @var int
     */
    public $verschluesselungsalgorithmus = 13;
    /** @var Bin Binary, max length: 512 */
    public $wertDesAlgorithmusparametersSchluessel;
    /**
     * 5: Symmetrischer Schlüssel (nicht zugelassen) This is the recommended dummy value for PIN/TAN.
     * 6: Symmetrischer Schlüssel, verschlüsselt mit einem öffentlichen Schlüssel bei RAH und RDH (KYP).
     * @var int
     */
    public $bezeichnerFuerAlgorithmusparameterSchluessel = 5; // Dummy for PIN/TAN
    /** @var int Allowed values: 1: Initialization value, clear text (IVC) */
    public $bezeichnerFuerAlgorithmusparameterIv = 1; // The only possible value.
    /** @var string|null Max length: 512 Not allowed for PIN/TAN */
    public $wertDesAlgorithmusparametersIv;

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
