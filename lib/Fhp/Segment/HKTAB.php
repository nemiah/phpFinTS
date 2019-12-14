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

    public function getName(): string
    {
        return static::NAME;
    }
}
