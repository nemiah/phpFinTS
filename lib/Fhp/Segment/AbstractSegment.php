<?php

namespace Fhp\Segment;

/**
 * Abstract Class AbstractSegment.
 *
 * @package Fhp\Segment
 */
abstract class AbstractSegment implements SegmentInterface
{
    const SEGMENT_SEPARATOR = "'";
    const DEFAULT_COUNTRY_CODE = 280;

    protected $type;
    protected $segmentNumber;
    protected $version;
    protected $dataElements;

    /**
     * AbstractSegment constructor.
     *
     * @param $type
     * @param $segmentNumber
     * @param $version
     * @param array $dataElements
     */
    public function __construct($type, $segmentNumber, $version, array $dataElements = array())
    {
        $this->type = strtoupper($type);
        $this->version = $version;
        $this->segmentNumber = $segmentNumber;
        $this->dataElements = $dataElements;
    }

    public function setDataElements(array $dataElements = array())
    {
        $this->dataElements = $dataElements;
    }

    public function getDataElements()
    {
        return $this->dataElements;
    }

    public function toString()
    {
        $string = $this->type . ':' . $this->segmentNumber . ':' . $this->version;

        foreach ($this->dataElements as $de) {
            $string .= '+' . (string) $de;
        }

        return $string . static::SEGMENT_SEPARATOR;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function humanReadable($translateCodes = false)
    {
        return str_replace(
            ["'", '+'],
            [PHP_EOL, PHP_EOL . "  " ],
            $translateCodes
                ? NameMapping::translateResponse($this->toString())
                : $this->toString()
        );
    }

    public function getSegmentNumber()
    {
        return $this->segmentNumber;
    }

    public function getVersion()
    {
        return $this->version;
    }
}
