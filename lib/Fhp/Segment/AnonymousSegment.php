<?php

namespace Fhp\Segment;

/**
 * Class AnonymousSegment
 *
 * A fallback for segments that were received from the server but are not implemented in this library.
 *
 * @package Fhp\Segment
 */
final class AnonymousSegment extends BaseSegment
{
    /**
     * The type plus version of the segment, i.e. the class name of the class that would normally implement it.
     * This is redundant with super::$segmentkopf, but it's useful to repeat here so that it shows up in a debugger.
     * @var string
     */
    public $type;

    /**
     * Contains the data elements of the segment. Some of them can be scalar values (represented as strings), and others
     * can be nested data element groups of strings.
     *
     * NOTE: This field is intentionally private and does not have a getter. Reading this field anywhere besides for
     * debugging purposes (e.g. in an interactive debugger or with print_r()) is wrong, please create a concrete
     * subclass of BaseSegment instead.
     *
     * @var string[]|string[][]
     */
    private $elements = [];

    /**
     * @param Segmentkopf $segmentkopf
     * @param string[]|string[][] $elements
     */
    public function __construct($segmentkopf, $elements)
    {
        $this->segmentkopf = $segmentkopf;
        $this->type = $segmentkopf->segmentkennung . 'v' . $segmentkopf->segmentversion;
        $this->elements = $elements;
    }

    public function getDescriptor()
    {
        throw new \RuntimeException("AnonymousSegments do not have a descriptor");
    }

    public function validate()
    {
        // Do nothing, anonymous segments are always valid.
    }

    /**
     * Just to override the super factory.
     */
    public static function createEmpty()
    {
        // Note: createEmpty() normally runs the constructor and then fills the Segmentkopf, but that is not possible
        // for AnonymousSegment. Callers should just call the constructor itself.
        throw new \RuntimeException("AnonymousSegment::createEmpty() is not allowed");
    }
}
