<?php

namespace Fhp\Protocol;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\HNVSK\HNVSKv3;

/**
 * Collects segments and assigns them segment numbers in order to form a {@link Message}.
 */
class MessageBuilder
{
    /**
     * The number of the first segment added to $plainSegments. Number 1 is HNHBK (not actually present, though), number
     * 2 is HNVSK, so the first content segment is 3.
     */
    const SEGMENT_NUMBER_OFFSET = 3;

    /**
     * This is where the builder collects the (unencrypted/unwrapped) segments as they are being added.
     * @var BaseSegment[]
     */
    public $segments = [];

    /** @return MessageBuilder A new instance. */
    public static function create()
    {
        return new MessageBuilder();
    }

    /**
     * @param BaseSegment|BaseSegment[] $segments The segment(s) to be added. Note that the segment number will be
     *     determined dynamically and written to the same segment instance that was passed in here.
     * @return $this The same instance for chaining.
     */
    public function add($segments)
    {
        if (is_array($segments)) {
            foreach ($segments as $segment) {
                $this->addInternal($segment);
            }
        } else {
            $this->addInternal($segments);
        }
        return $this;
    }

    private function addInternal($segment)
    {
        if ($segment->segmentkopf === null) {
            throw new \InvalidArgumentException(
                'Segment lacks Segmentkopf, maybe you called ctor instead of createEmpty()');
        }
        $segment->segmentkopf->segmentnummer = count($this->segments) + static::SEGMENT_NUMBER_OFFSET;
        if ($segment->segmentkopf->segmentnummer >= HNVSKv3::SEGMENT_NUMBER) {
            throw new \InvalidArgumentException('Too many segments');
        }
        $this->segments[] = $segment;
    }

    // Note: There is no single build() function, use Message::createWrappedMessage() instead.
}
