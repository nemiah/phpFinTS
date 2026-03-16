<?php

namespace Fhp\Segment;

class Segmentkopf extends BaseDeg
{
    /**
     * The name/type of the segment, e.g. "HITANS", corresponding to {@link SegmentDescriptor::$kennung}.
     * Max length: 6
     */
    public string $segmentkennung;

    /**
     * A number to refer to the segment within a message. Similar to an index, but they don't technically have to be
     * consecutive within a message.
     */
    public int $segmentnummer;

    /**
     * Version of the segment, corresponding to {@link SegmentDescriptor::$version}.
     */
    public int $segmentversion;

    /**
     * Not allowed in requests, optionally present in responses.
     * In a response message, this refers to the {@link $segmentnummer} of a segment in the request message.
     */
    public ?int $bezugselement = null;
}
