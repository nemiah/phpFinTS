<?php

namespace Fhp\Segment;

use Fhp\DataElementGroups\EncryptionAlgorithm;
use Fhp\DataElementGroups\KeyName;
use Fhp\DataElementGroups\SecurityDateTime;
use Fhp\DataElementGroups\SecurityIdentificationDetails;
use Fhp\DataElementGroups\SecurityProfile;

/**
 * Class HNVSK (Verschlüsselungskopf)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20130718_final_version.pdf
 * Section: B.5.3
 */
class HNVSK extends AbstractSegment
{
    const NAME = 'HNVSK';
    const VERSION = 3;

    const SECURITY_SUPPLIER_ROLE_ISS = 1;
    const SECURITY_SUPPLIER_ROLE_CON = 3;
    const SECURITY_SUPPLIER_ROLE_WIT = 4;

    const COMPRESSION_NONE = 0;
    const COMPRESSION_LZW = 1;
    const COMPRESSION_COM = 2;
    const COMPRESSION_LZSS = 3;
    const COMPRESSION_LZHUFF = 4;
    const COMPRESSION_ZIP = 5;
    const COMPRESSION_GZIP = 6;
    const COMPRESSION_BZIP2 = 7;
    const COMPRESSION_NEGOTIATE = 999;

    /**
     * HNVSK constructor.
     */
    public function __construct(
        int $segmentNumber,
        string $bankCode,
        string $userName,
        int $systemId = 0,
        int $securitySupplierRole = self::SECURITY_SUPPLIER_ROLE_ISS,
        int $countryCode = self::DEFAULT_COUNTRY_CODE,
        int $compression = self::COMPRESSION_NONE,
        int $pinTanVersion = SecurityProfile::PROFILE_VERSION_1
    ) {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            [
                new SecurityProfile(SecurityProfile::PROFILE_PIN, $pinTanVersion),
                998, // Just informational / invalid for PIN/TAN,
                $securitySupplierRole,
                new SecurityIdentificationDetails(SecurityIdentificationDetails::CID_NONE, $systemId),
                new SecurityDateTime(),
                new EncryptionAlgorithm(),
                new KeyName($countryCode, $bankCode, $userName),
                $compression,
            ]
        );
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
