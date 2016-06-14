<?php

namespace Fhp\Message;

use Fhp\Segment\HNHBK;
use Fhp\Segment\SegmentInterface;

class AbstractMessage
{
    const MSG_HEADER_SEGMENT = 'HNHBK';
    const MSG_HEADER_VERSION = 3;
    const MSG_HEADER_SEG_NUMBER = 1;
    const MSG_HBCI_VERSION = '300';

    const OPT_PINTAN_MECH = 'pintan_mechanism';

    protected $segments = array();
    protected $dialogId = 0;
    protected $messageNumber = 1;

    protected function addSegment(SegmentInterface $segment)
    {
        $this->segments[] = $segment;
    }

    public function getSegments()
    {
        return $this->segments;
    }

    public function setDialogId($dialogId)
    {
        $this->dialogId = $dialogId;
    }

    public function getDialogId()
    {
        return $this->dialogId;
    }

    public function setMessageNumber($number)
    {
        $this->messageNumber = $number;
    }

    public function getMessageNumber()
    {
        return $this->messageNumber;
    }

    public function toString()
    {
        $string = (string) $this->buildMessageHeader();

        foreach ($this->segments as $segment) {
            $string .= (string) $segment;
        }

        return $string;
    }

    public function __toString()
    {
        return $this->toString();
    }

    protected function buildMessageHeader()
    {
        $len = 0;
        foreach ($this->segments as $segment) {
            $len += strlen($segment);
        }

        return new HNHBK($len, $this->dialogId, $this->messageNumber);
    }
}
