<?php

namespace Fhp\Segment;

use Fhp\DataElementGroups\HashAlgorithm;
use Fhp\DataElementGroups\KeyName;
use Fhp\DataElementGroups\SecurityDateTime;
use Fhp\DataElementGroups\SecurityIdentificationDetails;
use Fhp\DataElementGroups\SecurityProfile;
use Fhp\DataElementGroups\SignatureAlgorithm;

/**
 * Class HNSHK (Signaturkopf)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_HBCI_Rel_20130718_final_version.pdf
 * Section: B.5.1
 */
class HNSHK extends AbstractSegment
{
    const NAME = 'HNSHK';
    const VERSION = 4;

    const SECURITY_FUNC_NRO = 1; // Non-Repudiation of Origin, für RAH, RDH (NRO)
    const SECURITY_FUNC_AUT = 2; // Message Origin Authentication, für RAH, RDH und DDV (AUT)
    const SECURITY_FUNC_ENC = 4; // Encryption, Verschlüsselung und evtl. Komprimierung (ENC)
    const SECURITY_FUNC_999 = 999;

    const SECURITY_BOUNDARY_SHM = 1; // Signaturkopf und HBCI-Nutzdaten (SHM)
    const SECURITY_BOUNDARY_SHT = 2; // Von Signaturkopf bis Signaturabschluss (SHT)

    const SECURITY_SUPPLIER_ROLE_ISS = 1; // Der Unterzeichner ist Herausgeber der signierten Nachricht, z.B. Erfasser oder Erstsignatur (ISS)
    const SECURITY_SUPPLIER_ROLE_CON = 3; // Der Unterzeichner unterstützt den Inhalt der Nachricht, z.B. bei Zweitsignatur (CON)
    const SECURITY_SUPPLIER_ROLE_WIT = 4; // Der Unterzeichner ist Zeuge, aber für den Inhalt der Nachricht nicht verantwortlich, z.B. Übermittler, welcher nicht Erfasser ist (WIT)

    /**
     * HNSHK constructor.
     */
    public function __construct(
        int $segmentNumber,
        string $securityReference,
        string $countryCode,
        string $bankCode,
        string $userName,
        int $systemId = 0,
        int $securityFunction = self::SECURITY_FUNC_999,
        int $securityBoundary = self::SECURITY_BOUNDARY_SHM,
        int $securitySupplierRole = self::SECURITY_SUPPLIER_ROLE_ISS,
        int $pinTanVersion = SecurityProfile::PROFILE_VERSION_1
    ) {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            [
                new SecurityProfile(SecurityProfile::PROFILE_PIN, $pinTanVersion), //2
                $securityFunction, //3
                $securityReference, //4
                $securityBoundary, //5
                $securitySupplierRole, //6
                new SecurityIdentificationDetails(SecurityIdentificationDetails::CID_NONE, $systemId), //7
                1, //8
                new SecurityDateTime(), //9
                new HashAlgorithm(), //10
                new SignatureAlgorithm(), //11
                new KeyName($countryCode, $bankCode, $userName, KeyName::KEY_TYPE_SIGNATURE),                //12
            ]
        );
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
