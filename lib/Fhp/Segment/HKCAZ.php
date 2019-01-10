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
 *
 * @package Fhp\Segment
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
     * @param int $version
     * @param int $segmentNumber
     * @param Kti $kti
     * @param string $camtFormat
     * @param array $allAccounts
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string|null $touchdown
     */
    public function __construct(
        $version,
        $segmentNumber,
        $kti,
        $camtFormat,
        $allAccounts,
        \DateTime $from,
        \DateTime $to,
        $touchdown = null
    ) {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            array(
                $kti,
                $camtFormat,
                $allAccounts,
                new Dat($from),
                new Dat($to),
                null,
                $touchdown
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }
}
