<?php

namespace Fhp\Adapter\Exception;

class CurlException extends AdapterException
{
    protected $curlInfo;

    public function __construct($message, $code = 0, \Exception $previous = null, $curlInfo)
    {
        parent::__construct($message, $code, $previous);
        $this->curlInfo = $curlInfo;
    }

    public function getCurlInfo()
    {
        return $this->curlInfo;
    }
}
