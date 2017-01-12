<?php

namespace Fhp\Adapter;

use Fhp\Adapter\Exception\AdapterException;
use Fhp\Adapter\Exception\CurlException;
use Fhp\Message\AbstractMessage;

/**
 * Class Curl
 * @package Fhp\Adapter
 */
class Curl implements AdapterInterface
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var resource
     */
    protected $curlHandle;

    /**
     * @var mixed
     */
    protected $lastResponseInfo;

    /**
     * Curl constructor.
     *
     * @param string $host
     * @param int $port
     * @throws AdapterException
     * @throws CurlException
     */
    public function __construct($host, $port)
    {
        if (!is_integer($port) || (int) $port <= 0) {
            throw new AdapterException('Invalid port number');
        }

        $this->host = (string) $host;
        $this->port = (int) $port;
        $this->curlHandle = curl_init();

        curl_setopt($this->curlHandle, CURLOPT_SSLVERSION, 1);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->curlHandle, CURLOPT_USERAGENT, "FHP-lib");
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->host);
        curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($this->curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curlHandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->curlHandle, CURLOPT_ENCODING, '');
        curl_setopt($this->curlHandle, CURLOPT_MAXREDIRS, 0);
        curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, array("cache-control: no-cache", 'Content-Type: text/plain'));
    }

    /**
     * @param AbstractMessage $message
     * @return string
     * @throws CurlException
     */
    public function send(AbstractMessage $message)
    {
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, base64_encode($message->toString()));
        $response = curl_exec($this->curlHandle);
        $this->lastResponseInfo = curl_getinfo($this->curlHandle);

        if (false === $response) {
            throw new CurlException(
                'Failed connection to ' . $this->host . ': ' . curl_error($this->curlHandle),
                curl_errno($this->curlHandle),
                null,
                $this->lastResponseInfo
            );
        }

        $statusCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);

        if ($statusCode < 200 || $statusCode > 299) {
            throw new CurlException('Bad response with status code ' . $statusCode, 0, null, $this->lastResponseInfo);
        }

        return base64_decode($response);
    }

    /**
     * Gets curl info for last request / response.
     *
     * @return mixed
     */
    public function getLastResponseInfo()
    {
        return $this->lastResponseInfo;
    }
}
