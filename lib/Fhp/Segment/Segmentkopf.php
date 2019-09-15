<?php


namespace Fhp\Segment;


class Segmentkopf extends BaseDeg
{
    /**
     * The name/type of the segment, e.g. "HITANS", corresponding to {@link SegmentDescriptor#kennung}.
     * Max length: 6
     * @var string
     */
    public $segmentkennung;

    /**
     * A number to refer to the segment within a message. Similar to an index, but they don't technically have to be
     * consecutive within a message.
     * @var integer
     */
    public $segmentnummer;

    /**
     * Version of the segment, corresponding to {@link SegmentDescriptor#version}.
     * @var integer
     */
    public $segmentversion;

    /**
     * Not allowed in requests, optionally present in responses.
     * In a response message, this refers to the {@link #segmentnummer} of a segment in the request message.
     * @var integer|null
     */
    public $bezugselement;
}
