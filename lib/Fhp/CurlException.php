<?php

namespace Fhp;

class CurlException extends \Exception
{
    /**
     * @var array
     */
    protected $curlInfo;

    /**
     * @var string|null
     */
    protected $response;

    /**
     * CurlException constructor.
     *
     * @param string      $message
     * @param string|null $response
     * @param int         $code
     * @param mixed       $curlInfo
     */
    public function __construct($message, $response, $code = 0, $curlInfo = [])
    {
        parent::__construct($message, $code, null);
        $this->response = $response;
        $this->curlInfo = $curlInfo;
    }

    /**
     * @return string|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Gets the curl info from request / response.
     *
     * @return array
     */
    public function getCurlInfo()
    {
        return $this->curlInfo;
    }
}
