<?php

namespace Fhp\Dialog\Exception;

/**
 * Class FailedRequestException.
 *
 * Transforms HBCI error to exception.
 *
 * @package Fhp\Dialog\Exception
 */
class FailedRequestException extends \Exception
{
    /**
     * @var array
     */
    protected $summary = array();

    /**
     * @var int
     */
    protected $responseCode = 0;

    /**
     * @var string
     */
    protected $responseMessage;

    /**
     * FailedRequestException constructor.
     *
     * @param array $summary
     */
    public function __construct(array $summary)
    {
        $this->summary = $summary;
        $keys = array_keys($summary);

        $this->responseCode = 0;
        $this->responseMessage = 'Unknown error';

        if (count($summary) == 1) {
            $this->responseCode = (int) $keys[0];
            $this->responseMessage = array_shift($summary);
        } elseif (count($summary) > 1) {
            foreach ($summary as $scode => $smsg) {
                if (0 === strpos($smsg, '*')) {
                    $this->responseCode = (int) $scode;
                    $this->responseMessage = substr($smsg, 1);
                }
            }
        }
        
        parent::__construct('Request Failed: ' . $this->responseMessage, $this->responseCode);
    }

    /**
     * @return array
     */
    public function getCodes()
    {
        return array_keys($this->summary);
    }

    /**
     * @return array
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @return string
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * @return string
     */
    public function getResponseMessages()
    {
        return implode(', ', $this->summary);
    }
}
