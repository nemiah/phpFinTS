<?php

namespace Fhp\Segment;

/**
 * Class Segment
 * @package Fhp\Segment
 */
class Segment extends AbstractSegment
{
    /**
     * @param string $string
     * @return Segment
     */
    public static function createFromString($string)
    {
        $lines = explode('+', $string);
        $header = array_shift($lines);
        $headerParts = explode(':', $header);

        $name = strtoupper($headerParts[0]);
        $segmentNumber = $headerParts[1];
        $version = $headerParts[2];

        return new self($name, 0, $segmentNumber, $version, $lines);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->type;
    }
}
