<?php

namespace Fhp\Response;

use Fhp\Message\AbstractMessage;
use Fhp\Segment\AbstractSegment;
use Fhp\Segment\NameMapping;

/**
 * Class Response
 *
 * @package Fhp\Response
 */
class Response
{
    /** @var string */
    public $rawResponse;

    /** @var string */
    protected $response;

    /** @var array */
    protected $segments = array();

    /** @var string */
    protected $dialogId;

    /** @var string */
    protected $systemId;

	private $dialog;
	
    /**
     * Response constructor.
     *
     * @param string $rawResponse
     */
    public function __construct($rawResponse, \Fhp\Dialog\Dialog $dialog = null)
    {
        if ($rawResponse instanceof Response) {
            $rawResponse = $rawResponse->rawResponse;
        }

        $this->rawResponse = $rawResponse;
        $this->response = $this->unwrapEncryptedMsg($rawResponse);
		
		$rawResponse = preg_replace("/\@([0-9]*)\@HIRMG/", "@$1@'HIRMG", $rawResponse);
        $this->segments = preg_split("#'(?=[A-Z]{4,}:\d|')#", $rawResponse);

		$this->dialog = $dialog;
	}
	
	public function isTANRequest()
	{
		return get_class($this) == "Fhp\Response\GetTANRequest";
	}
	
	function getDialog()
	{
		return $this->dialog;
	}

    /**
     * Extracts dialog ID from response.
     *
     * @return string|null
     * @throws \Exception
     */
    public function getDialogId()
    {
        $segment = $this->findSegment('HNHBK');

        if (null === $segment) {
            throw new \Exception('Could not find element HNHBK. Invalid response?');
        }

        return $this->getSegmentIndex(4, $segment);
    }

    /**
     * Extracts bank name from response.
     *
     * @return string|null
     */
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

    /**
     * Some kind of HBCI pagination.
     *
     * @param AbstractMessage $message
     *
     * @return array
     */
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

    /**
     * Extracts supported TAN mechanisms from response.
     *
     * @return array
     */
    public function getSupportedTanMechanisms()
    {
        $segments = $this->findSegments('HIRMS');
        // @todo create method to get reference element from request
        foreach ($segments as $segment) {
            $segment = $this->splitSegment($segment);
            array_shift($segment);
            foreach ($segment as $seg) {
                list($id, $msg) = explode('::', $seg, 2);
                if ("3920" == $id) {
                    if (preg_match_all('/\d{3}/', $msg, $matches)) {
                        return $matches[0];
                    }
                }
            }
        }

        return array();
    }

    /**
     * @return int
     */
    public function getHksalMaxVersion()
    {
        return $this->getSegmentMaxVersion('HISALS');
    }

    /**
     * @return int
     */
    public function getHkkazMaxVersion()
    {
        return $this->getSegmentMaxVersion('HIKAZS');
    }

    /**
     * Checks if request / response was successful.
     *
     * @return bool
     */
    public function isSuccess()
    {
        $summary = $this->getMessageSummary();

        foreach ($summary as $code => $message) {
            if ("9" == substr($code, 0, 1)) {
                return false;
            }
        }
		
        $summary = $this->getSegmentSummary();

        foreach ($summary as $code => $message) {
            if ("9" == substr($code, 0, 1)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getMessageSummary()
    {
        return $this->getSummaryBySegment('HIRMG');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSegmentSummary()
    {
        return $this->getSummaryBySegment('HIRMS');
    }

    /**
     * @param string $name
     *
     * @return array
     * @throws \Exception
     */
	protected function getSummaryBySegment($name)
	{
		if (!in_array($name, array('HIRMS', 'HIRMG'))) {
			throw new \Exception('Invalid segment for message summary. Only HIRMS and HIRMG supported');
		}

		$result = array();
		foreach ($this->findSegments($name) AS $segment) {
			$segment = $this->splitSegment($segment);
			array_shift($segment);
			foreach ($segment as $de) {
				$de = $this->splitDeg($de);
				$result[$de[0]] = $de[2];
			}
		}

		return $result;
	}

    /**
     * @param string $segmentName
     *
     * @return int
     */
    public function getSegmentMaxVersion($segmentName)
    {
        $version = 3;
        $segments = $this->findSegments($segmentName);
        foreach ($segments as $s) {
            $parts = $this->splitSegment($s);
            $segmentHeader = $this->splitDeg($parts[0]);
            $curVersion = (int) $segmentHeader[2];
            if ($curVersion > $version) {
                $version = $curVersion;
            }
        }

        return $version;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getSystemId()
    {
        $segment = $this->findSegment('HISYN');

        if (!preg_match('/HISYN:\d+:\d+:\d+\+(.+)/', $segment, $matches)) {
            throw new \Exception('Could not determine system id.');
        }

        return $matches[1];
    }

    /**
     * @param bool $translateCodes
     *
     * @return string
     */
    public function humanReadable($translateCodes = false)
    {
        return str_replace(
            array("'", '+'),
            array(PHP_EOL, PHP_EOL . "  "),
            $translateCodes
                ? NameMapping::translateResponse($this->rawResponse)
                : $this->rawResponse
        );
    }

    /**
     * @param string          $name
     * @param AbstractSegment $reference
     *
     * @return string|null
     */
    protected function findSegmentForReference($name, AbstractSegment $reference)
    {
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

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function findSegment($name)
    {
        return $this->findSegments($name, true);
    }

    /**
     * @param string $name
     * @param bool   $one
     *
     * @return array|null|string
     */
    protected function findSegments($name, $one = false)
    {
        $found = $one ? null : array();

        foreach ($this->segments as $segment) {
            $split = explode(':', $segment, 2);

            $segment = $this->conformToUtf8($segment);

            if ($split[0] == $name) {
                if ($one) {
                    return $segment;
                }
                $found[] = $segment;
            }
        }

        return $found;
    }

    protected function conformToUtf8($string)
    {
        return iconv('ISO-8859-1', 'UTF-8', $string);
    }

    /**
     * @param $segment
     *
     * @return array
     */
    public function splitSegment($segment, $fix = true)
    {
		preg_match("@\<\?xml.+Document\>@", $segment, $matches);
		$segment = preg_replace("@\<\?xml.+Document\>@", "EXTRACTEDXML", $segment);
		
        $parts = preg_split('/\+(?<!\?\+)/', $segment);

        foreach ($parts as &$part) {
			if($fix)
	            $part = str_replace('?+', '+', $part);
			if(trim($part) != "" AND strpos($part, "EXTRACTEDXML") > 0 AND isset($matches[0]))
				$part = str_replace("EXTRACTEDXML", $matches[0], $part);
        }

        return $parts;
    }
	
    /**
     * @param $deg
     *
     * @return array
     */
    protected function splitDeg($deg)
    {
        return explode(':', $deg);
    }

    /**
     * @param int $idx
     * @param     $segment
     *
     * @return string|null
     */
    protected function getSegmentIndex($idx, $segment)
    {
        $segment = $this->splitSegment($segment);
        if (isset($segment[$idx - 1])) {
            return $segment[$idx - 1];
        }

        return null;
    }

    /**
     * @param string $response
     *
     * @return string
     */
    protected function unwrapEncryptedMsg($response)
    {
        if (preg_match('/HNVSD:\d+:\d+\+@\d+@(.+)\'\'/', $response, $matches)) {
            return $matches[1];
        }

        return $response;
    }
}
