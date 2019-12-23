<?php

namespace Fhp\Segment;

/**
 * Class HKSAL (Saldenabfrage)
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.2.1.2
 */
class HKSAL extends AbstractSegment
{
    const NAME = 'HKSAL';
    const VERSION = 7;
    const ALL_ACCOUNTS_N = 'N';
    const ALL_ACCOUNTS_Y = 'J';

    /**
     * HKSAL constructor.
     * @param mixed $ktv
     */
    public function __construct(int $version, int $segmentNumber, $ktv, bool $allAccounts)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            $version,
            [
                $ktv,
                $allAccounts,
            ]
        );
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
