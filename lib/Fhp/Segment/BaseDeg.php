<?php

namespace Fhp\Segment;

use Fhp\Syntax\Parser;
use Fhp\Syntax\Serializer;

/**
 * Base class for Data Element Groups (Datenelement-Gruppen; DEGs).
 */
abstract class BaseDeg
{
    /**
     * Reference to the descriptor for this type of segment.
     * @var DegDescriptor|null
     */
    private $descriptor;

    /**
     * @return DegDescriptor The descriptor for this Deg type.
     */
    public function getDescriptor()
    {
        if (!isset($this->descriptor)) {
            $this->descriptor = DegDescriptor::get(static::class);
        }
        return $this->descriptor;
    }

    public function __debugInfo()
    {
        $result = get_object_vars($this);
        unset($result['descriptor']); // Don't include descriptor in debug output, to avoid clutter.
        return $result;
    }

    /**
     * @throws \InvalidArgumentException If any element in this DEG is invalid.
     */
    public function validate()
    {
        $this->getDescriptor()->validateObject($this);
    }

    /**
     * Short-hand for {@link Serializer#serializeDeg()}.
     * @return string The HBCI wire format representation of this DEG, terminated by the segment delimiter.
     */
    public function serialize()
    {
        return Serializer::serializeDeg($this, $this->getDescriptor());
    }

    /**
     * Convenience function for {@link Parser#parseGroup()}. This function should not be called on BaseDeg itself, but
     * only on one of its sub-classes.
     * @param string $rawElements The serialized wire format for a data element group.
     * @return static The parsed value.
     */
    public static function parse($rawElements)
    {
        return Parser::parseDeg($rawElements, static::class);
    }
}
