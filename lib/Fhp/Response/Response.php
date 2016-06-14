<?php

namespace Fhp\Response;

use Fhp\Message\AbstractMessage;
use Fhp\Message\Message;
use Fhp\Segment\AbstractSegment;
use Fhp\Segment\NameMapping;

class Response
{
    public $rawResponse;
    protected $response;
    protected $segments = array();
    protected $dialogId;
    protected $systemId;

    public function __construct($rawResponse)
    {
        if ($rawResponse instanceof Initialization) {
            $rawResponse = $rawResponse->rawResponse;
        }

        $this->rawResponse = $rawResponse;
        $this->response    = $this->unwrapEncryptedMsg($rawResponse);
        $this->segments = explode("'", $rawResponse);
    }

    public function getDialogId()
    {
        $segment = $this->findSegment('HNHBK');

        if (null == $segment) {
            throw new \Exception('Could not find element HNHBK. Invalid response?');
        }

        return $this->getSegmentIndex(4, $segment);
    }

    public function getBankName()
    {
        $bankName = null;
        $segment = $this->findSegment('HIBPA');
        if (null != $segment) {
            $split = $this->splitSegment($segment);
            if (isset($split[3])) {
                $bankName = $split[3];
            }
        }

        return $bankName;
    }

    public function getTouchDowns(AbstractMessage $message)
    {
        $touchdown = array();
        $messageSegments = $message->getEncryptedSegments();
        /** @var AbstractSegment $msgSeg */
        foreach ($messageSegments as $msgSeg) {
            $segment = $this->findSegmentForReference('HIRMS', $msgSeg);
            if (null != $segment) {
                $parts = $this->splitSegment($segment);
                // remove header
                array_shift($parts);
                foreach ($parts as $p) {
                    $pSplit = $this->splitDeg($p);
                    if ($pSplit[0] == 3040) {
                        $td = $pSplit[3];
                        $touchdown[$msgSeg->getName()] = $td;
                    }
                }
            }
        }

        return $touchdown;
    }

    public function getSupportedTanMechanisms()
    {
        $segments = $this->findSegments('HIRMS');
        // @todo create method to get reference element from request
        foreach ($segments as $segment) {
            $segment = $this->splitSegment($segment);
            array_shift($segment);
            foreach($segment as $seg) {
                list($id, $msg) = explode('::', $seg, 2);
                if ("3920" == $id) {
                    if (preg_match_all('/\d{3}/', $msg, $matches)) {
                        return $matches[0];
                    }
                }
            }
        }

        return false;
    }

    public function getHksalMaxVersion()
    {
        return $this->getSegmentMaxVersion('HISALS');
    }

    public function getHkkazMaxVersion()
    {
        return $this->getSegmentMaxVersion('HIKAZS');
    }

    public function isSuccess()
    {
        $summary = $this->getMessageSummary();

        foreach ($summary as $code => $message) {
            if ("9" == substr($code, 0, 1)) {
                return false;
            }
        }

        return true;
    }

    public function getMessageSummary()
    {
        return $this->getSummaryBySegment('HIRMG');
    }

    public function getSegmentSummary()
    {
        return $this->getSummaryBySegment('HIRMS');
    }

    protected function getSummaryBySegment($name)
    {
        if (!in_array($name, array('HIRMS', 'HIRMG'))) {
            throw new \Exception('Invalid segment for message summary. Only HIRMS and HIRMG supported');
        }

        $result = array();
        $segment = $this->findSegment($name);
        $segment = $this->splitSegment($segment);
        array_shift($segment);
        foreach ($segment as $de) {
            $de = $this->splitDeg($de);
            $result[$de[0]] = $de[2];
        }

        return $result;
    }

    public function getSegmentMaxVersion($segmentName)
    {
        $version = "3";
        $segments = $this->findSegments($segmentName);
        foreach ($segments as $s) {
            $parts = $this->splitSegment($s);
            $segmentHeader = $this->splitDeg($parts[0]);
            $curVersion = $segmentHeader[2];
            if ($curVersion > $version) {
                $version = $curVersion;
            }
        }

        return $version;
    }

    public function getSystemId()
    {
        $segment = $this->findSegment('HISYN');

        if (!preg_match('/HISYN:\d+:\d+:\d+\+(.+)/', $segment, $matches)) {
            throw new \Exception('Could not determine system id.');
        }

        return $matches[1];
    }

    public function humanReadable($translateCodes = false)
    {
        return str_replace(
            ["'", '+'],
            [PHP_EOL, PHP_EOL . "  " ],
            $translateCodes
                ? NameMapping::translateResponse($this->rawResponse)
                : $this->rawResponse
        );
    }

    protected function findSegmentForReference($name, AbstractSegment $reference)
    {
        $result = null;
        $segments = $this->findSegments($name);
        foreach ($segments as $seg) {
            $segSplit = $this->splitSegment($seg);
            $segSplit = array_shift($segSplit);
            $segSplit = $this->splitDeg($segSplit);
            if ($segSplit[3] == $reference->getSegmentNumber()) {
                return $seg;
            }
        }

        return null;
    }

    protected function findSegment($name)
    {
        return $this->findSegments($name, true);
    }

    /**
     * @param $name
     * @param bool|false $one
     * @return array|null|string
     */
    protected function findSegments($name, $one = false)
    {
        $found = $one ? null : array();

        foreach ($this->segments as $segment) {
            $split = explode(':', $segment, 2);

            if ($split[0] == $name) {
                if ($one) {
                    return $segment;
                }
                $found[] = $segment;
            }
        }

        return $found;
    }

    protected function splitSegment($segment)
    {
        $parts = explode('+', $segment);

        return $parts;
    }

    protected function splitDeg($deg)
    {
        return explode(':', $deg);
    }

    protected function getSegmentIndex($idx, $segment)
    {
        $segment = $this->splitSegment($segment);
        if (isset($segment[$idx - 1])) {
            return $segment[$idx - 1];
        }

        return null;
    }

    protected function unwrapEncryptedMsg($response)
    {
        if (preg_match('/HNVSD:\d+:\d+\+@\d+@(.+)\'\'/', $response, $matches)) {
            return $matches[1];
        }

        return $response;
    }
}
