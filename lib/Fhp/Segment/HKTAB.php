<?php

namespace Fhp\Segment;

/**
 * Class HKTAB (request devices)
 * Segment type: Geschäftsvorfall
 */
class HKTAB extends AbstractSegment
{
    const NAME = 'HKTAB';
    const VERSION = 4;

    /**
     * HKTAB constructor.
     * @param int $segmentNumber
     */
    public function __construct(int $segmentNumber)
    {
        parent::__construct(
            static::NAME,
            $segmentNumber,
            static::VERSION,
            [
                0,
                'A',
            ]
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }
}
