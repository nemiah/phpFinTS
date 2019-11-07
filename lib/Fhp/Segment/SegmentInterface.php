<?php

namespace Fhp\Segment;

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

    /**
     * @return int
     */
    public function getVersion();

    /**
     * @return int
     */
    public function getSegmentNumber();
}
