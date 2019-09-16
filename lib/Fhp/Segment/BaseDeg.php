<?php

namespace Fhp\Segment;

use Fhp\Syntax\Parser;
use Fhp\Syntax\Serializer;

/**
 * Class BaseDeg
 *
 * Base class for Data Element Groups (Datenelement-Gruppen; DEGs).
 *
 * @package Fhp\Segment
 */
abstract class BaseDeg
{
    /**
     * Reference to the descriptor for this type of segment.
     * @var DegDescriptor
     */
    private $descriptor;

    public function __construct()
    {
        $this->descriptor = DegDescriptor::get(static::class);
    }

    public function getDescriptor()
    {
        return $this->descriptor;
    }

    /**
     * @throws \InvalidArgumentException If any element in this DEG is invalid.
     */
    public function validate()
    {
        $this->descriptor->validateObject($this);
    }

    /**
     * Short-hand for {@link Serializer#serializeDeg()}.
     * @return string The HBCI wire format representation of this DEG, terminated by the segment delimiter.
     */
    public function serialize()
    {
        return Serializer::serializeDeg($this);
    }

    /**
     * Convenience function for {@link Parser#parseGroup()}. This function should not be called on BaseDeg itself, but
     * only on one of its sub-classes.
     * @param string $rawElements The serialized wire format for a data element group.
     * @return BaseDeg The parsed value.
     */
    public static function parse($rawElements)
    {
        return Parser::parseDeg($rawElements, static::class);
    }
}
