<?php


namespace Fhp\Syntax;

use Fhp\Syntax\Delimiter;
use Fhp\Segment\BaseDeg;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\DegDescriptor;
use Fhp\Segment\ElementDescriptor;
use Fhp\Segment\SegmentDescriptor;
use Fhp\Segment\Segmentkopf;

/**
 * Class Parser
 *
 * Parses the FinTS wire format (aka. syntax) into Messages, Segments, Data Element Groups (DEG) and Data Elements (DE).
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section H.1 "Nachrichtensyntax"
 *
 * @package Fhp\Syntax
 */
abstract class Parser
{
    /**
     * The FinTs wire format specifies escaping with a question mark `?` for the syntax characters `+:'?@`. This
     * function splits strings delimited by one of these while honoring escaping within.
     *
     * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
     * Section H.1.3 "Entwertung"
     *
     * @param string $delimiter The delimiter around which to split.
     * @param string $str The raw string, usually a response from the server.
     * @return string[] The splitted substrings. Note that escaped characters inside will still be escaped.
     */
    public static function splitEscapedString($delimiter, $str)
    {
        if (empty($str)) return array();
        // Since most of the $delimiters used in FinTs are also special characters in regexes, we need to escape.
        $delimiter = preg_quote($delimiter, '/');
        // This regex uses a negated look-behind. Generally, the regex `(?<!foo)x` matches an `x` that is NOT preceded
        // by `foo`. In this case, we want to match on the split $delimiter when it is not preceded by the escape
        // character `?`, which we need to escape because it's a special character in regexes.
        return preg_split("/(?<!\\?)$delimiter/", $str);
    }

    /**
     * @param string $str The raw string, usually a response from the server.
     * @return string The string with the escaping removed.
     */
    public static function unescape($str)
    {
        return preg_replace('/\?([+:\'?@])/', '$1', $str);
    }

    /**
     * Parses a scalar value aka. "Datenelement" (DE).
     *
     * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
     * Section B.4 Datenformate
     *
     * @param string $rawValue The raw value (wire format).
     * @param string $type The PHP type that we need. This should support exactly the values for which
     *     {@link ElementDescriptor#isScalarType()} returns true.
     * @return mixed The parsed value of type $type.
     */
    public static function parseDataElement($rawValue, $type)
    {
        if ($type === 'int' || $type === 'integer') {
            if (!is_numeric($rawValue)) {
                throw new \InvalidArgumentException("Invalid int: $rawValue");
            }
            return intval($rawValue);
        } elseif ($type === 'float') {
            $rawValue = str_replace(',', '.', $rawValue, $numCommas);
            if (!is_numeric($rawValue) || $numCommas !== 1) {
                throw new \InvalidArgumentException("Invalid float: $rawValue");
            }
            return floatval($rawValue);
        } elseif ($type === 'bool' || $type === 'boolean') {
            if ($rawValue === 'J') return true;
            if ($rawValue === 'N') return false;
            throw new \InvalidArgumentException("Invalid bool: $rawValue");
        } elseif ($type === 'string') {
            return static::unescape($rawValue);
        } else {
            throw new \RuntimeException("Unsupported type $type");
        }
    }

    /**
     * @param string $rawElements The serialized wire format for a data element group.
     * @param string $type The type (PHP class name) of the Deg to be parsed.
     * @return BaseDeg The parsed value, of type
     */
    public static function parseDeg($rawElements, $type)
    {
        $rawElements = static::splitEscapedString(Delimiter::GROUP, $rawElements);
        list($result, $offset) = static::parseDegElements($rawElements, $type);
        if ($offset < count($rawElements)) {
            throw new \InvalidArgumentException("Only read $offset elements: " . print_r($rawElements, true));
        }
        return $result;
    }

    /**
     * @param string[] $rawElements The serialized wire format for a series of elements (already splitted). This array
     *     will be modified in that the elements that were consumed are removed from the beginning.
     * @param string $type The type (PHP class name) of the Deg to be parsed, defaults to the class on which
     *     this function is called.
     * @param integer $offset The position in $rawElements to be read next.
     * @return array (BaseDeg, integer) The parsed value, which has the given $type, and the offset at which parsing
     *     should continue. The difference between this returned offset and the $offset was passed in is the number of
     *     elements that this function call consumed.
     */
    private static function parseDegElements($rawElements, $type, $offset = 0)
    {
        if ($type === null) $type = static::class;
        $descriptor = DegDescriptor::get($type);
        $result = new $type();
        $expectedIndex = 0;
        // The iteration order guarantees that $index is strictly monotonically increasing, but there can be gaps.
        foreach ($descriptor->elements as $index => $elementDescriptor) {
            $offset += ($index - $expectedIndex); // Adjust for skipped indices.
            $numRepetitions = $elementDescriptor->repeated === 0 ? 1 : $elementDescriptor->repeated;
            $expectedIndex += $numRepetitions; // Advance to next expected elementDescriptor index.

            // Skip optional elements that are not present.
            if (!isset($rawElements[$offset]) || $rawElements[$offset] === '') {
                if ($elementDescriptor->optional) {
                    $offset++;
                    continue;
                }
                throw new \InvalidArgumentException("Missing field $elementDescriptor->field");
            }

            // Parse element (possibly multiple values recursively).
            try {
                for ($repetition = 0; $repetition < $numRepetitions; $repetition++) {
                    if ($offset >= count($rawElements)) {
                        break; // End of input reached
                    }
                    if (is_string($elementDescriptor->type)) { // Scalar type / DE
                        if ($rawElements[$offset] === '' && $repetition >= 1) { // Skip empty repeated entries.
                            $offset++;
                            continue;
                        }
                        $value = static::parseDataElement($rawElements[$offset], $elementDescriptor->type);
                        $offset++;
                    } else { // Nested DEG, will consume a certain number of elements and adjust the $offset accordingly.
                        list($value, $offset) =
                            static::parseDegElements($rawElements, $elementDescriptor->type->name, $offset);
                    }
                    if ($elementDescriptor->repeated === 0) {
                        $result->{$elementDescriptor->field} = $value;
                    } else {
                        $result->{$elementDescriptor->field}[] = $value;
                    }
                }
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException("Failed to parse $descriptor->class::$elementDescriptor->field: $e");
            }
        }
        return array($result, $offset);
    }

    /**
     * @param string $rawSegment The serialized wire format for a single segment (segment delimiter may be present at
     *     the end, or not).
     * @param string $type The type (PHP class name) of the segment to be parsed.
     * @return BaseSegment The parsed segment of type $type.
     */
    public static function parseSegment($rawSegment, $type)
    {
        $descriptor = SegmentDescriptor::get($type);
        if (substr($rawSegment, -1) === Delimiter::SEGMENT) {
            $rawSegment = substr($rawSegment, 0, -1); // Strip segment delimiter at the end, if present.
        }
        $rawElements = static::splitEscapedString(Delimiter::ELEMENT, $rawSegment);
        if (empty($rawElements)) {
            throw new \InvalidArgumentException("Invalid segment: $rawSegment");
        }

        /** @var Segmentkopf $segmentkopf */
        $segmentkopf = static::parseDeg($rawElements[0], Segmentkopf::class);
        if ($segmentkopf->segmentkennung !== $descriptor->kennung) {
            throw new \InvalidArgumentException("Invalid segment type $segmentkopf->segmentkennung for $type");
        }
        if ($segmentkopf->segmentversion !== $descriptor->version) {
            throw new \InvalidArgumentException("Invalid version $segmentkopf->segmentversion for $type");
        }

        $result = new $type();
        $result->segmentkopf = $segmentkopf;
        // The iteration order guarantees that $index is strictly monotonically increasing, but there can be gaps.
        foreach ($descriptor->elements as $index => $elementDescriptor) {
            if (!isset($rawElements[$index]) || $rawElements[$index] === '') {
                if ($elementDescriptor->optional) {
                    continue;
                }
                throw new \InvalidArgumentException("Missing field $elementDescriptor->field");
            }

            if ($elementDescriptor->repeated === 0) {
                $result->{$elementDescriptor->field} =
                    static::parseSegmentElement($rawElements[$index], $elementDescriptor);
            } else {
                for ($repetition = 0; $repetition < $elementDescriptor->repeated; $repetition++) {
                    if ($index + $repetition >= count($rawElements)) {
                        break; // End of input reached.
                    }
                    if ($rawElements[$index + $repetition] !== '') { // Skip empty entries.
                        $result->{$elementDescriptor->field}[$repetition] =
                            static::parseSegmentElement($rawElements[$index + $repetition], $elementDescriptor);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $rawElement The raw content (unparsed wire format) of an element, which can either be a single
     *     Data Element (DE) or a group (DEG), as determined by the descriptor.
     * @param ElementDescriptor $descriptor The descriptor that describes the expected format of the element.
     * @return mixed The parsed value.
     */
    private static function parseSegmentElement($rawElement, $descriptor)
    {
        return is_string($descriptor->type)
            ? static::parseDataElement($rawElement, $descriptor->type)
            : static::parseDeg($rawElement, $descriptor->type->name);
    }
}
