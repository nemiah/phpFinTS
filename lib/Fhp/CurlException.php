<?php

namespace Fhp;

/**
 * Class CurlException
 * @package Fhp
 */
class CurlException extends \Exception
{
    /**
     * @var mixed
     */
    protected $curlInfo;

    /**
     * CurlException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @param mixed $curlInfo
     */
    public function __construct($message, $code = 0, \Exception $previous = null, $curlInfo = "")
    {
        parent::__construct($message, $code, $previous);

        $this->curlInfo = $curlInfo;
    }

    /**
     * Gets the curl info from request / response.
     *
     * @return mixed
     */
    public function getCurlInfo()
    {
        return $this->curlInfo;
    }
}
