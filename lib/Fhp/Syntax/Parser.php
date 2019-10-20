<?php


namespace Fhp\Syntax;

use Fhp\DataTypes\Bin;
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
     * function splits strings delimited by one of these while honoring escaping and binary blocks marked with a
     * `@<size>@` header within the string.
     *
     * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
     * Section H.1.3 "Entwertung"
     *
     * @param string $delimiter The delimiter around which to split.
     * @param string $str The raw string, usually a response from the server.
     * @param bool $trailingDelimiter If this is true, the delimiter is expected at the very end, and also kept at the
     *     end of each returned substring, i.e. it's considered part of each item instead of a delimiter between items.
     * @return string[] The splitted substrings. Note that escaped characters inside will still be escaped.
     */
    public static function splitEscapedString($delimiter, $str, $trailingDelimiter = false)
    {
        if (empty($str)) return array();
        // Since most of the $delimiters used in FinTs are also special characters in regexes, we need to escape.
        $delimiter = preg_quote($delimiter, '/');
        $nextBegin = 0;
        $offset = 0;
        $result = [];
        while (true) {
            // Walk to the next syntax character of interest and handle it respectively.
            $ret = preg_match("/\\?|@([0-9]+)@|$delimiter/", $str, $match, PREG_OFFSET_CAPTURE, $offset);
            if ($ret === false) {
                throw new \RuntimeException("preg_match failed on $str");
            }
            if ($ret === 0) { // There is no more syntax character behind $offset.
                if ($trailingDelimiter) {
                    // The last character should have been a delimiter, so there should be no content remaining.
                    if ($nextBegin !== strlen($str)) {
                        throw new \InvalidArgumentException(
                            "Unexpected content after last delimiter: " . substr($str, $nextBegin));
                    }
                } else {
                    // Anything behind the last delimiter forms the last substring.
                    $result[] = substr($str, $nextBegin);
                }
                break;
            }
            $matchedStr = $match[0][0]; // $match[0] refers to the entire matched string. [0] has the content
            $matchedOffset = $match[0][1]; // and [1] has the offset within $str.
            if ($matchedStr === '?') {
                // It's an escape character, so we should ignore this character and the next one.
                $offset = $matchedOffset + 2;
                if ($offset > strlen($str)) {
                    throw new \InvalidArgumentException("Input ends on unescaped escape character.");
                }
            } elseif ($matchedStr[0] === Delimiter::BINARY) {
                // It's a block binary data, which we should skip entirely.
                $binaryLength = $match[1][0]; // $match[1] refers to the first (and only) capture group in the regex.
                if (!is_numeric($binaryLength)) throw new \AssertionError();
                // Note: The FinTS specification says that the length of the binary block is given in bytes (not
                // characters) and PHP's string functions like substr() or preg_match() also operate on byte offsets, so
                // this is fine.
                $offset = $matchedOffset + strlen($matchedStr) + intval($binaryLength);
                if ($offset > strlen($str)) {
                    throw new \InvalidArgumentException(
                        "Incomplete binary block at offset $matchedOffset, declared length $binaryLength, but "
                        . "only has " . (strlen($str) - $matchedOffset - strlen($matchedStr)) . " bytes left");
                }
            } else {
                // The delimiter was matched, so output one splitted string and advance past the delimiter.
                $result[] = substr($str, $nextBegin, $matchedOffset - $nextBegin + ($trailingDelimiter ? 1 : 0));
                $nextBegin = $matchedOffset + strlen($matchedStr);
                $offset = $nextBegin;
            }
        }
        return $result;
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
            // Convert ISO-8859-1 (FinTS wire format encoding) to UTF-8 (PHP's encoding)
            return utf8_encode(static::unescape($rawValue));
        } else {
            throw new \RuntimeException("Unsupported type $type");
        }
    }

    /**
     * @param string $rawValue The raw value (wire format), e.g. "@4@abcd".
     * @return Bin The parsed value.
     */
    public static function parseBinaryBlock($rawValue)
    {
        $delimiterPos = strpos($rawValue, Delimiter::BINARY, 1);
        if (empty($rawValue) || $rawValue[0] !== Delimiter::BINARY || $delimiterPos === false) {
            throw new \InvalidArgumentException("Expected binary block header, got $rawValue");
        }
        $lengthStr = substr($rawValue, 1, $delimiterPos - 1);
        if (!is_numeric($lengthStr)) {
            throw new \InvalidArgumentException("Invalid binary block length: $lengthStr");
        }
        $length = intval($lengthStr);
        $result = new Bin(substr($rawValue, $delimiterPos + 1));
        // Note: The length is measured in wire format encoding, i.e. ISO-8859-1, so we need to convert back here.
        $actualLength = strlen(utf8_decode($result->getData()));
        if ($actualLength !== $length) {
            throw new \InvalidArgumentException("Expected binary block of length $length, got $actualLength");
        }
        return $result;
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
                    if (is_string($elementDescriptor->type) // Scalar type / DE
                        || $elementDescriptor->type->getName() === Bin::class) {
                        if ($rawElements[$offset] === '' && $repetition >= 1) { // Skip empty repeated entries.
                            $offset++;
                            continue;
                        }
                        if (is_string($elementDescriptor->type)) {
                            $value = static::parseDataElement($rawElements[$offset], $elementDescriptor->type);
                        } else {
                            $value = static::parseBinaryBlock($rawElements[$offset]);
                        }
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
     * @param string $rawSegment The serialized wire format for a single segment (segment delimiter must be present at
     *     the end).
     * @param string $type The type (PHP class name) of the segment to be parsed.
     * @return BaseSegment The parsed segment of type $type.
     */
    public static function parseSegment($rawSegment, $type)
    {
        $rawElements = static::splitIntoSegmentElements($rawSegment);
        $descriptor = SegmentDescriptor::get($type);
        $result = new $type();
        // The iteration order guarantees that $index is strictly monotonically increasing, but there can be gaps.
        foreach ($descriptor->elements as $index => $elementDescriptor) {
            if (!isset($rawElements[$index]) || $rawElements[$index] === '') {
                if ($elementDescriptor->optional) {
                    continue;
                }
                throw new \InvalidArgumentException("Missing field $type.$elementDescriptor->field");
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
        if ($result->segmentkopf->segmentkennung !== $descriptor->kennung) {
            throw new \InvalidArgumentException("Invalid segment type $result->segmentkopf->segmentkennung for $type");
        }
        if ($result->segmentkopf->segmentversion !== $descriptor->version) {
            throw new \InvalidArgumentException("Invalid version $result->segmentkopf->segmentversion for $type");
        }
        return $result;
    }

    /**
     * @param string $rawSegment The serialized wire format for a single segment incl delimiter at the end.
     * @return string[] The segment splitted into raw elements.
     */
    private static function splitIntoSegmentElements($rawSegment)
    {
        if (substr($rawSegment, -1) !== Delimiter::SEGMENT) {
            throw new \InvalidArgumentException("Raw segment does not end with delimiter: $rawSegment");
        }
        $rawSegment = substr($rawSegment, 0, -1); // Strip segment delimiter at the end.
        $rawElements = static::splitEscapedString(Delimiter::ELEMENT, $rawSegment);
        if (empty($rawElements)) {
            throw new \InvalidArgumentException("Invalid segment: $rawSegment");
        }
        return $rawElements;
    }

    /**
     * @param string $rawElement The raw content (unparsed wire format) of an element, which can either be a single
     *     Data Element (DE) or a group (DEG), as determined by the descriptor.
     * @param ElementDescriptor $descriptor The descriptor that describes the expected format of the element.
     * @return mixed The parsed value.
     */
    private static function parseSegmentElement($rawElement, $descriptor)
    {
        if (is_string($descriptor->type)) { // Scalar value / DE
            return static::parseDataElement($rawElement, $descriptor->type);
        } elseif ($descriptor->type->getName() === Bin::class) {
            return static::parseBinaryBlock($rawElement);
        } else {
            return static::parseDeg($rawElement, $descriptor->type->name);
        }
    }
}
