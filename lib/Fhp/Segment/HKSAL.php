<?php

namespace Fhp\Segment;

/**
 * Class HKSAL (Saldenabfrage)
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.2
 *
 * @package Fhp\Segment
 */
class HKSAL extends AbstractSegment
{
    const NAME = 'HKSAL';
    const VERSION = 7;
    const ALL_ACCOUNTS_N = 'N';
    const ALL_ACCOUNTS_Y = 'J';

    /**
     * HKSAL constructor.
     * @param int $version
     * @param int $segmentNumber
     * @param mixed $ktv
     * @param array $allAccounts
     */
    public function __construct($version, $segmentNumber, $ktv, $allAccounts)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            array(
                $ktv,
                $allAccounts,
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
