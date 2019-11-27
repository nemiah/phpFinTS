<?php

namespace Fhp\Message;

use Fhp\Segment\HNHBK;
use Fhp\Segment\SegmentInterface;

class AbstractMessage
{
    const OPT_PINTAN_MECH = 'pintan_mechanism';

    /**
     * @var array
     */
    protected $segments = [];

    /**
     * @var string
     */
    protected $systemId;

    /**
     * @var string
     */
    protected $dialogId;

    /**
     * @var int
     */
    protected $messageNumber = 1;

    /**
     * Adds a segment to the message.
     *
     * @param SegmentInterface $segment
     */
    protected function addSegment(SegmentInterface $segment)
    {
        $this->segments[] = $segment;
    }

    /**
     * Gets all segments of a message.
     *
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @return string
     */
    public function getSystemId(): string
    {
        return $this->systemId;
    }

    /**
     * Sets the dialog ID.
     *
     * @param $dialogId
     */
    public function setDialogId($dialogId)
    {
        $this->dialogId = $dialogId;
    }

    /**
     * Gets the dialog ID.
     *
     * @return string
     */
    public function getDialogId(): string
    {
        return $this->dialogId;
    }

    /**
     * Sets the message number.
     *
     * @param int $number
     */
    public function setMessageNumber($number)
    {
        $this->messageNumber = (int) $number;
    }

    /**
     * Gets the message number.
     *
     * @return int
     */
    public function getMessageNumber()
    {
        return $this->messageNumber;
    }

    /**
     * Transform message to HBCI string.
     *
     * @return string
     */
    public function toString()
    {
        $string = (string) $this->buildMessageHeader();

        foreach ($this->segments as $segment) {
            $string .= (string) $segment;
        }

        return $string;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Builds the message header.
     *
     * @return HNHBK
     */
    protected function buildMessageHeader()
    {
        $len = 0;
        foreach ($this->segments as $segment) {
            $len += strlen($segment);
        }

        return new HNHBK($len, $this->dialogId, $this->messageNumber);
    }
}
