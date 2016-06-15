<?php

namespace Fhp\Segment;

/**
 * Interface SegmentInterface
 * @package Fhp\Segment
 */
interface SegmentInterface
{
    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function __toString();

    /**
     * Gets the name of the segment.
     *
     * @return string
     */
    public function getName();
}
