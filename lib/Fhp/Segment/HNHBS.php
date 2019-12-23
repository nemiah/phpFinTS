<?php

namespace Fhp\Segment;

/**
 * Class HNHBS (Nachrichtenabschluss)
 * Segment type: Administration
 *
 * @link: http://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2011-06-14_final_version.pdf
 * Section: B.5.3
 */
class HNHBS extends AbstractSegment
{
    const NAME = 'HNHBS';
    const VERSION = 1;

    /**
     * HNHBS constructor.
     */
    public function __construct(
        int $segmentNumber,
        int $messageNumber
    ) {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            [$messageNumber]
        );
    }

    public function getName(): string
    {
        return static::NAME;
    }
}
