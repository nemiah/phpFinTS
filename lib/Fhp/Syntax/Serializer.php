<?php

namespace Fhp\Syntax;

use Fhp\Segment\BaseDeg;
use Fhp\Segment\BaseSegment;

// Polyfill for PHP < 7.3
if (!function_exists("array_key_last")) {
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
     * @param mixed $value A scalar (DE) value.
     * @param string $type The PHP type of this value. This should support exactly the values for which
     *     {@link ElementDescriptor#isScalarType()} returns true.
     * @return string The HBCI wire format representation of the value.
     */
    public static function serializeDataElement($value, $type)
    {
        if ($type === 'int' || $type === 'integer' || $type === 'string') {
            return strval($value);
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
     * @param BaseDeg $deg The data element group to be serialized.
     * @return string The HBCI wire format representation of the DEG.
     */
    public static function serializeDeg($deg)
    {
        return implode(Delimiter::GROUP, Serializer::serializeElements($deg));
    }

    /**
     * @param BaseSegment $segment The segment to be serialized.
     * @return string The HBCI wire format representation of the segment, terminated by the segment delimiter.
     */
    public static function serializeSegment($segment)
    {
        $serializedElements = static::serializeElements($segment);
        if (isset($serializedElements[0]) && $serializedElements[0] !== '') throw new \AssertionError();
        $serializedElements[0] = $segment->segmentkopf->serialize();
        return implode(Delimiter::ELEMENT, $serializedElements) . Delimiter::SEGMENT;
    }

    /**
     * @param BaseSegment|BaseDeg $obj An object to be serialized.
     * @return string[] A partial serialization of that object, namely an array with all of its elements serialized
     *     independently, and at the right indices (i.e. the returned array may contain empty strings as gaps/buffers).
     */
    private static function serializeElements($obj)
    {
        $serializedElements = array();
        foreach ($obj->getDescriptor()->elements as $index => $elementDescriptor) {
            $value = $obj->{$elementDescriptor->field};
            if ($value === null) continue;
            if (isset($serializedElements[$index])) throw new \AssertionError();
            if ($elementDescriptor->repeated === 0) {
                $serializedElements[$index] = static::serializeElement($value, $elementDescriptor->type);
            } else {
                foreach ($value as $offset => $item) {
                    $serializedElements[$index + $offset] = static::serializeElement($item, $elementDescriptor->type);
                }
            }
        }
        static::fillMissingKeys($serializedElements, '');
        return $serializedElements;
    }

    private static function serializeElement($value, $type)
    {
        return is_string($type) ? static::serializeDataElement($value, $type) : static::serializeDeg($value);
    }

    /**
     * Public for testing only.
     * @param array $arr An array with numeric sorted keys, e.g. `[0 => 'a', 2 => 'b', 4 => 'c']`. After this function
     *     returns, all missing keys (gaps) will be filled in with the $value, e.g. if `$value == 'X'` the result would
     *     be `[0 => 'a', 1 => 'X', 2 => 'b', 3 => 'X', 4 => 'c'].
     * @param mixed $value The value to fill in.
     */
    public static function fillMissingKeys(&$arr, $value)
    {
        if (empty($arr)) return;
        $lastKey = array_key_last($arr);
        if (!is_numeric($lastKey)) throw new \InvalidArgumentException("Keys must be numeric, got $lastKey");
        for ($key = 0; $key < $lastKey; $key++) {
            if (!array_key_exists($key, $arr)) {
                $arr[$key] = $value;
            }
        }
        ksort($arr);
    }
}
