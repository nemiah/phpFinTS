<?php

namespace Fhp\Segment;

use Fhp\DataTypes\Ktv;

/**
 * Class HKSPA (SEPA-Kontoverbindung anfordern)
 * Segment type: Geschäftsvorfall
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.1.3
 *
 * @package Fhp\Segment
 */
class HKSPA extends AbstractSegment
{
    const NAME = 'HKSPA';
    const VERSION = 1;

    /**
     * HKSPA constructor.
     * @param int $segmentNumber
     * @param Ktv|null $ktv
     */
    public function __construct($segmentNumber, Ktv $ktv = null)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            array($ktv)
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
