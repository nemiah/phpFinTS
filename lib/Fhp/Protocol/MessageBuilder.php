<?php

namespace Fhp\Protocol;

use Fhp\Segment\BaseSegment;

/**
 * Collects segments and assigns them segment numbers in order to form a {@link Message}.
 */
class MessageBuilder
{
    /**
     * This is where the builder collects the (unencrypted/unwrapped) segments as they are being added.
     * @var BaseSegment[]
     */
    public $segments = [];

    /** @return MessageBuilder A new instance. */
    public static function create(): MessageBuilder
    {
        return new MessageBuilder();
    }

    /**
     * @param BaseSegment|BaseSegment[] $segments The segment(s) to be added.
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
        $this->segments[] = $segment;
    }

    // Note: There is no single build() function, use Message::createWrappedMessage() instead.
}
