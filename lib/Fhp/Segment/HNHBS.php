<?php

namespace Fhp\Segment;

use Fhp\DataTypes\Kik;

/**
 * Class HNHBS (Nachrichtenabschluss)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2011-06-14_final_version.pdf
 * Section: B.5.3
 *
 * @package Fhp\Segment
 */
class HNHBS extends AbstractSegment
{
    const NAME = 'HNHBS';
    const VERSION = 1;

    /**
     * HNHBS constructor.
     * @param int $segmentNumber
     * @param int $messageNumber
     */
    public function __construct(
        $segmentNumber,
        $messageNumber
    ) {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            array($messageNumber)
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
