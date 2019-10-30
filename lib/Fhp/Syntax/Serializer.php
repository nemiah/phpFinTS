<?php

namespace Fhp\Syntax;

use Fhp\DataTypes\Bin;
use Fhp\Segment\AnonymousSegment;
use Fhp\Segment\BaseDeg;
use Fhp\Segment\BaseDescriptor;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\DegDescriptor;
use Fhp\Segment\SegmentDescriptor;

// Polyfill for PHP < 7.3
if (!function_exists('array_key_last') && !function_exists('Fhp\\Syntax\\array_key_last')) {
    function array_key_last($array)
    {
        if (!is_array($array) || empty($array)) {
            return NULL;
        }
        return array_keys($array)[count($array) - 1];
    }
}

abstract class Serializer
{

    /**
     * Escapes syntax characters (delimiters).
     * @param string $str The unescaped string.
     * @return string The escaped string in wire format.
     */
    public static function escape($str)
    {
        return preg_replace('/([+:\'?@])/', '?$1', $str);
    }

    /**
     * @param mixed|null $value A scalar (DE) value.
     * @param string $type The PHP type of this value. This should support exactly the values for which
     *     {@link ElementDescriptor#isScalarType()} returns true.
     * @return string The HBCI wire format representation of the value.
     */
    public static function serializeDataElement($value, $type)
    {
        if ($value === null) return '';
        if ($type === 'int' || $type === 'integer' || $type === 'string') {
            // Convert UTF-8 (PHP's encoding) to ISO-8859-1 (FinTS wire format encoding)
            return static::escape(utf8_decode(strval($value)));
        } elseif ($type === 'float') {
            // Format with fixed 2 decimal places (there has to be some limit, and the specification does not specify
            // one), then trim zeros from the end.
            return preg_replace('/0+$/', '', number_format($value, 2, ',', ''));
        } elseif ($type === 'bool' || $type === 'boolean') {
            return $value ? 'J' : 'N';
        } else {
            throw new \RuntimeException("Unsupported type $type");
        }
    }

    /**
     * @param BaseDeg|null $deg The data element group to be serialized. If null, all fields are implicitly null.
     * @param DegDescriptor $descriptor The descriptor for the DEG type.
     * @return string The HBCI wire format representation of the DEG.
     */
    public static function serializeDeg($deg, $descriptor)
    {
        $serializedElements = Serializer::serializeElements($deg, $descriptor);
        return implode(Delimiter::GROUP, static::flattenAndTrimEnd($serializedElements));
    }

    /**
     * @param BaseSegment $segment The segment to be serialized.
     * @return string The HBCI wire format representation of the segment, in ISO-8859-1 encoding, terminated by the
     *     segment delimiter.
     */
    public static function serializeSegment($segment)
    {
        if ($segment instanceof AnonymousSegment) {
            throw new \InvalidArgumentException("Cannot serialize anonymous segments");
        }
        $serializedElements = static::serializeElements($segment, $segment->getDescriptor());
        return implode(Delimiter::ELEMENT, static::flattenAndTrimEnd($serializedElements)) . Delimiter::SEGMENT;
    }

    /**
     * @param BaseSegment|BaseDeg|null $obj An object to be serialized. If null, all fields are implicitly null.
     * @param BaseDescriptor $descriptor The descriptor for the object to be serialized.
     * @return array A partial serialization of that object, namely a (possibly nested) array with all of its elements
     *     serialized independently, and at the right indices. In order to put subsequent elements in the right
     *     position, the returned array may contain emtpy strings as gaps/buffers in the middle (for subsequent elements
     *     in $obj) and/or at the end (for subsequent elements added by the caller for data following $obj).
     */
    private static function serializeElements($obj, $descriptor)
    {
        $isSegment = $descriptor instanceof SegmentDescriptor;
        $serializedElements = array();
        $lastKey = array_key_last($descriptor->elements);
        for ($index = 0; $index <= $lastKey; $index++) {
            if (!array_key_exists($index, $descriptor->elements)) {
                $serializedElements[$index] = '';
                continue;
            }
            $elementDescriptor = $descriptor->elements[$index];
            $value = $obj === null ? null : $obj->{$elementDescriptor->field};
            if (isset($serializedElements[$index])) throw new \AssertionError("Duplicate index $index");
            if ($elementDescriptor->repeated === 0) {
                $serializedElements[$index] = static::serializeElement($value, $elementDescriptor->type, $isSegment);
            } else {
                if ($value !== null && !is_array($value)) {
                    throw new \InvalidArgumentException(
                        "Expected array value for $descriptor->class.$elementDescriptor->field, got: $value");
                }
                for ($repetition = 0; $repetition < $elementDescriptor->repeated; $repetition++) {
                    $serializedElements[$index + $repetition] = static::serializeElement(
                        $value === null || $repetition >= count($value) ? null : $value[$repetition],
                        $elementDescriptor->type, $isSegment);
                }
            }
        }
        return $serializedElements;
    }

    /**
     * @param mixed|null $value The value to be serialized.
     * @param string|\ReflectionClass $type The type of the value.
     * @param boolean $fullySerialize If true, the result is always a string, complex values are imploded as a DEG.
     * @return string|array The serialized value. In case $type is a complex type and $fullySerialize is false, this
     *     returns a (possibly nested) array of strings.
     */
    private static function serializeElement($value, $type, $fullySerialize)
    {
        if (is_string($type)) {
            return static::serializeDataElement($value, $type);
        } elseif ($type->getName() === Bin::class) {
            /** @var Bin|null $value */
            return $value === null ? '' : $value->toString();
        } elseif ($fullySerialize) {
            return static::serializeDeg($value, DegDescriptor::get($type->name));
        } else {
            return static::serializeElements($value, DegDescriptor::get($type->name));
        }
    }

    /**
     * @param array $elements A possibly nested array of string values.
     * @return string[] A flat array with the same string values (using in-order tree traversal), but empty values
     *     removed from the end.
     */
    private static function flattenAndTrimEnd($elements)
    {
        $result = [];
        $nonemptyLength = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($elements)) as $element) {
            $result[] = $element;
            if ($element !== '') $nonemptyLength = count($result);
        }
        return array_slice($result, 0, $nonemptyLength);
    }
}
