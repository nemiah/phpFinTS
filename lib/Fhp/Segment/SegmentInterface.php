<?php

namespace Fhp\Segment;

interface SegmentInterface
{
    /**
     * Returns string representation of object.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Gets the name of the segment.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getVersion(): int;

    /**
     * @return int
     */
    public function getSegmentNumber(): int;
}
