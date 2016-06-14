<?php

namespace Fhp\Dialog\Exception;

class FailedRequestException extends \Exception
{
    protected $summary = [];
    protected $responseCode = 0;
    protected $responseMessage;
    
    public function __construct(array $summary)
    {
        $this->summary = $summary;
        $keys = array_keys($summary);

        $this->responseCode = 0;
        $this->responseMessage = 'Unknown error';

        if (count($summary) == 1) {
            $this->responseCode = $keys[0];
            $this->responseMessage = array_shift($summary);
        } elseif (count($summary) > 1) {
            foreach ($summary as $scode => $smsg) {
                if (0 === strpos($smsg, '*')) {
                    $this->responseCode = $scode;
                    $this->responseMessage = substr($smsg, 1);
                }
            }
        }
        
        parent::__construct('Request Failed: ' . $this->responseMessage, $this->responseCode);
    }
    
    public function getCodes()
    {
        return array_keys($this->summary);
    }
    
    public function getSummary()
    {
        return $this->summary;
    }
    
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    public function getResponseMessages()
    {
        return implode(', ', $this->summary);
    }
}
