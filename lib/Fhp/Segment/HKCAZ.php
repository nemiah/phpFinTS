<?php

namespace Fhp\Segment;

use Fhp\DataTypes\Dat;
use Fhp\DataTypes\Kti;

/**
 * Class HKCAZ (Kontoumsätze/Zeitraum (camt))
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.3.1.1.1
 */
class HKCAZ extends AbstractSegment
{
    const NAME = 'HKCAZ';
    const ALL_ACCOUNTS_N = 'N';
    const ALL_ACCOUNTS_Y = 'J';
    const CAMT_FORMAT = 'camt.052.001.02';
    const CAMT_FORMAT_FQ = 'urn:iso:std:iso:20022:tech:xsd:' . self::CAMT_FORMAT;

    /**
     * HKCAZ constructor.
     */
    public function __construct(
        int $version,
        int $segmentNumber,
        Kti $kti,
        string $camtFormat,
        array $allAccounts,
        \DateTime $from,
        \DateTime $to,
        ?string $touchdown = null
    ) {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            [
                $kti,
                $camtFormat,
                $allAccounts,
                new Dat($from),
                new Dat($to),
                null,
                $touchdown,
            ]
        );
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
