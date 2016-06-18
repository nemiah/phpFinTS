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

    /** @var string */
    protected $type;
    /** @var int */
    protected $segmentNumber;
    /** @var int */
    protected $version;
    /** @var array */
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

    /**
     * @param array $dataElements
     */
    public function setDataElements(array $dataElements = array())
    {
        $this->dataElements = $dataElements;
    }

    /**
     * @return array
     */
    public function getDataElements()
    {
        return $this->dataElements;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $string = $this->type . ':' . $this->segmentNumber . ':' . $this->version;

        foreach ($this->dataElements as $de) {
            $string .= '+' . (string) $de;
        }

        return $string . static::SEGMENT_SEPARATOR;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @param bool $translateCodes
     * @return string
     */
    public function humanReadable($translateCodes = false)
    {
        return str_replace(
            array("'", '+'),
            array(PHP_EOL, PHP_EOL . "  "),
            $translateCodes
                ? NameMapping::translateResponse($this->toString())
                : $this->toString()
        );
    }

    /**
     * @return int
     */
    public function getSegmentNumber()
    {
        return $this->segmentNumber;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
