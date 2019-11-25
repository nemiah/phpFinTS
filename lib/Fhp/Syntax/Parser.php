<?php

namespace Fhp\Syntax;

use Fhp\DataTypes\Bin;
use Fhp\Segment\AnonymousSegment;
use Fhp\Segment\BaseDeg;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\DegDescriptor;
use Fhp\Segment\ElementDescriptor;
use Fhp\Segment\SegmentDescriptor;
use Fhp\Segment\Segmentkopf;

// Polyfill for PHP < 7.3
if (!function_exists('array_key_last') && !function_exists('Fhp\\Syntax\\array_key_last')) {
    function array_key_last($array)
    {
        if (!is_array($array) || empty($array)) {
            return null;
        }
        return array_keys($array)[count($array) - 1];
    }
}

/**
 * Parses the FinTS wire format (aka. syntax) into Messages, Segments, Data Element Groups (DEG) and Data Elements (DE).
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section H.1 "Nachrichtensyntax"
 */
abstract class Parser
{
    /** @var string Name of the PHP namespace under which all the segments are stored. */
    const SEGMENT_NAMESPACE = 'Fhp\Segment';

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
        if (empty($str)) {
            return [];
        }
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
                            'Unexpected content after last delimiter: ' . substr($str, $nextBegin));
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
                    throw new \InvalidArgumentException('Input ends on unescaped escape character.');
                }
            } elseif ($matchedStr[0] === Delimiter::BINARY) {
                // It's a block binary data, which we should skip entirely.
                $binaryLength = $match[1][0]; // $match[1] refers to the first (and only) capture group in the regex.
                if (!is_numeric($binaryLength)) {
                    throw new \AssertionError("Invalid binary length $binaryLength");
                }
                // Note: The FinTS specification says that the length of the binary block is given in bytes (not
                // characters) and PHP's string functions like substr() or preg_match() also operate on byte offsets, so
                // this is fine.
                $offset = $matchedOffset + strlen($matchedStr) + intval($binaryLength);
                if ($offset > strlen($str)) {
                    throw new \InvalidArgumentException(
                        "Incomplete binary block at offset $matchedOffset, declared length $binaryLength, but "
                        . 'only has ' . (strlen($str) - $matchedOffset - strlen($matchedStr)) . ' bytes left');
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
     * @return mixed|null The parsed value of type $type, null if the $rawValue was empty.
     */
    public static function parseDataElement($rawValue, $type)
    {
        if ($rawValue === '') {
            return null;
        }
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
            if ($rawValue === 'J') {
                return true;
            }
            if ($rawValue === 'N') {
                return false;
            }
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
     * @return Bin|null The parsed value, or null if $rawValue was empty.
     */
    public static function parseBinaryBlock($rawValue)
    {
        if ($rawValue === '') {
            return null;
        }

        $delimiterPos = strpos($rawValue, Delimiter::BINARY, 1);
        if (
            empty($rawValue) ||
            substr($rawValue, 0, 1) !== Delimiter::BINARY ||
            $delimiterPos === false
        ) {
            throw new \InvalidArgumentException("Expected binary block header, got $rawValue");
        }

        $lengthStr = substr($rawValue, 1, $delimiterPos - 1);
        if (!is_numeric($lengthStr)) {
            throw new \InvalidArgumentException("Invalid binary block length: $lengthStr");
        }

        $length = intval($lengthStr);
        $result = new Bin(substr($rawValue, $delimiterPos + 1));

        $actualLength = strlen($result->getData());
        if ($actualLength !== $length) {
            throw new \InvalidArgumentException("Expected binary block of length $length, got $actualLength");
        }
        return $result;
    }

    /**
     * @param string $rawElements The serialized wire format for a data element group.
     * @param string $type The type (PHP class name) of the Deg to be parsed.
     * @param bool $allowEmpty If true, this returns either a valid DEG, or null if *all* the fields were empty.
     * @return BaseDeg|null The parsed value, of type $type, or null if all fields were empty and $allowEmpty is true.
     */
    public static function parseDeg($rawElements, $type, $allowEmpty = false)
    {
        $rawElements = static::splitEscapedString(Delimiter::GROUP, $rawElements);
        list($result, $offset) = static::parseDegElements($rawElements, $type, $allowEmpty);
        if ($offset < count($rawElements)) {
            throw new \InvalidArgumentException(
                "Expected only $offset elements, but got " . count($rawElements) . ': ' . print_r($rawElements, true));
        }
        return $result;
    }

    /**
     * @param string[] $rawElements The serialized wire format for a series of elements (already splitted). This array
     *     will be modified in that the elements that were consumed are removed from the beginning.
     * @param string $type The type (PHP class name) of the Deg to be parsed, defaults to the class on which
     *     this function is called.
     * @param bool $allowEmpty If true, this returns either a valid DEG, or null if *all* the fields were empty.
     * @param int $offset The position in $rawElements to be read next.
     * @return array (BaseDeg|null, integer)
     *     1. The parsed value, which has the given $type or is null in case all the fields were empty and $allowEmpty
     *        is true.
     *     2. The offset at which parsing should continue. The difference between this returned offset and the $offset
     *        that was passed in is the number of elements that this function call consumed.
     */
    private static function parseDegElements($rawElements, $type, $allowEmpty = false, $offset = 0)
    {
        if ($type === null) {
            $type = static::class;
        }
        $descriptor = DegDescriptor::get($type);
        $result = new $type();
        $expectedIndex = 0;
        $allEmpty = true;
        $missingFieldError = null; // When $allowEmpty, we need to tolerate errors at first, but maybe throw them later.
        // The iteration order guarantees that $index is strictly monotonically increasing, but there can be gaps.
        foreach ($descriptor->elements as $index => $elementDescriptor) {
            $offset += ($index - $expectedIndex); // Adjust for skipped indices.
            $numRepetitions = $elementDescriptor->repeated === 0 ? 1 : $elementDescriptor->repeated;
            $expectedIndex += $numRepetitions; // Advance to next expected elementDescriptor index.
            $isSingleField = is_string($elementDescriptor->type) // Scalar type / DE
                || $elementDescriptor->type->getName() === Bin::class;

            // Skip optional single elements that are not present. Note that for elements with multiple fields we cannot
            // just skip because here we would only detect whether the first field is empty or not.
            if ($isSingleField && (!isset($rawElements[$offset]) || $rawElements[$offset] === '')) {
                if ($elementDescriptor->optional) {
                    ++$offset;
                    continue;
                } elseif ($missingFieldError === null) {
                    $missingFieldError = new \InvalidArgumentException("Missing field $type.$elementDescriptor->field");
                    if (!$allowEmpty) {
                        throw $missingFieldError;
                    }
                }
            }

            // Parse element (possibly multiple values recursively).
            try {
                for ($repetition = 0; $repetition < $numRepetitions; ++$repetition) {
                    if ($offset >= count($rawElements)) {
                        break; // End of input reached
                    }
                    if ($isSingleField) {
                        if ($rawElements[$offset] === '' && $repetition >= 1) { // Skip empty repeated entries.
                            ++$offset;
                            continue;
                        }
                        if (is_string($elementDescriptor->type)) {
                            $value = static::parseDataElement($rawElements[$offset], $elementDescriptor->type);
                        } else {
                            $value = static::parseBinaryBlock($rawElements[$offset]);
                        }
                        ++$offset;
                    } else { // Nested DEG, will consume a certain number of elements and adjust the $offset accordingly.
                        list($value, $offset) = static::parseDegElements(
                            $rawElements, $elementDescriptor->type->name,
                            $allowEmpty || $elementDescriptor->optional, $offset);
                    }
                    if ($value !== null) {
                        $allEmpty = false;
                    }
                    if ($elementDescriptor->repeated === 0) {
                        $result->{$elementDescriptor->field} = $value;
                    } elseif ($value !== null) {
                        $result->{$elementDescriptor->field}[] = $value;
                    }
                }
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException("Failed to parse $descriptor->class::$elementDescriptor->field: $e");
            }
        }
        if ($allEmpty && $allowEmpty) {
            return [null, $offset];
        }
        if ($missingFieldError !== null) {
            throw $missingFieldError;
        }
        return [$result, $offset];
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
        if (array_key_last($rawElements) > $descriptor->maxIndex) {
            throw new \InvalidArgumentException("Too many elements for $type: $rawSegment");
        }
        $result = new $type();
        // The iteration order guarantees that $index is strictly monotonically increasing, but there can be gaps.
        foreach ($descriptor->elements as $index => $elementDescriptor) {
            if (!isset($rawElements[$index]) || $rawElements[$index] === '') {
                if ($elementDescriptor->optional) {
                    continue;
                }
                throw new \InvalidArgumentException("Missing field $type.$elementDescriptor->field");
            }

            // Note: The handling of empty values may be incorrect here, parseSegmentElement() can return null.
            if ($elementDescriptor->repeated === 0) {
                $result->{$elementDescriptor->field} =
                    static::parseSegmentElement($rawElements[$index], $elementDescriptor);
            } else {
                for ($repetition = 0; $repetition < $elementDescriptor->repeated; ++$repetition) {
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
     * @param string $rawSegment The serialized wire format for a single segment (segment delimiter must be present at
     *     the end).
     * @return AnonymousSegment The segment parsed as an anonymous segment.
     */
    public static function parseAnonymousSegment($rawSegment)
    {
        $rawElements = static::splitIntoSegmentElements($rawSegment);
        return new AnonymousSegment(
            Segmentkopf::parse(array_shift($rawElements)),
            array_map(function ($rawElement) {
                if (empty($rawElement)) {
                    return null;
                }
                $subElements = static::splitEscapedString(Delimiter::GROUP, $rawElement);
                if (count($subElements) <= 1) {
                    return $rawElement;
                } // Asume it's not repeated.
                return $subElements;
            }, $rawElements));
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
     * @return mixed|null The parsed value, or null if it was empty.
     */
    private static function parseSegmentElement($rawElement, $descriptor)
    {
        if (is_string($descriptor->type)) { // Scalar value / DE
            return static::parseDataElement($rawElement, $descriptor->type);
        } elseif ($descriptor->type->getName() === Bin::class) {
            return static::parseBinaryBlock($rawElement);
        } else {
            return static::parseDeg($rawElement, $descriptor->type->name, $descriptor->optional);
        }
    }

    /**
     * @param string $rawSegment The serialized wire format for a single segment (segment delimiter must be present at
     *     the end).
     * @return BaseSegment The parsed segment, possibly an {@link AnonymousSegment}.
     */
    public static function detectAndParseSegment($rawSegment)
    {
        if (substr($rawSegment, -1) !== Delimiter::SEGMENT) {
            throw new \InvalidArgumentException("Raw segment does not end with delimiter: $rawSegment");
        }
        $firstElementDelimiter = strpos($rawSegment, Delimiter::ELEMENT);
        if ($firstElementDelimiter === false) {
            // Let's assume it's an empty segment, i.e. all of it is the header.
            $firstElementDelimiter = strlen($rawSegment) - 1; // Exclude the SEGMENT delimiter at the end.
        }
        /** @var Segmentkopf $segmentkopf */
        $segmentkopf = Segmentkopf::parse(substr($rawSegment, 0, $firstElementDelimiter));

        // Try the default class name Fhp\Segment\HABCD\HABCDvN.
        $segmentType = static::SEGMENT_NAMESPACE . '\\' . $segmentkopf->segmentkennung . '\\'
            . $segmentkopf->segmentkennung . 'v' . $segmentkopf->segmentversion;
        if (class_exists($segmentType)) {
            return static::parseSegment($rawSegment, $segmentType);
        }

        // Alternatively, allow Geschäftsvorfall segments (HKXYZ, HIXYZ and HIXYZS) to live in an abbreviated namespace,
        // i.e. like Fhp\Segment\XYZ\HKXYZSvN
        $segmentType = static::SEGMENT_NAMESPACE . '\\' . substr($segmentkopf->segmentkennung, 2, 3) . '\\'
            . $segmentkopf->segmentkennung . 'v' . $segmentkopf->segmentversion;
        if (class_exists($segmentType)) {
            return static::parseSegment($rawSegment, $segmentType);
        }

        // If the segment type is not implemented, fall back to an anonymous segment.
        return static::parseAnonymousSegment($rawSegment);
    }

    /**
     * @param string $rawSegments Concatenated segments in wire format.
     * @return BaseSegment[] The parsed segments.
     */
    public static function parseSegments($rawSegments)
    {
        if (empty($rawSegments)) {
            return [];
        }
        $rawSegments = static::splitEscapedString(Delimiter::SEGMENT, $rawSegments, true);
        return array_map([static::class, 'detectAndParseSegment'], $rawSegments);
    }

    /**
     * @deprecated Could be removed, if Response::$rawSegments is removed.
     * @param string $rawSegments
     * @return string[] RawSegments
     */
    public static function parseRawSegments($rawSegments)
    {
        if (empty($rawSegments)) {
            return [];
        }
        $rawSegments = static::splitEscapedString(Delimiter::SEGMENT, $rawSegments, true);

        // End delimiter must be removed for Response::rawSegments
        return array_map(function ($rawResponse) {
            if (substr($rawResponse, -1) == "'") {
                return substr($rawResponse, 0, -1);
            }
            return $rawResponse;
        }, $rawSegments);
    }
}
