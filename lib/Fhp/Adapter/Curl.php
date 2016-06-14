<?php

namespace Fhp\Adapter;

use Fhp\Adapter\Exception\CurlException;
use Fhp\Message\AbstractMessage;

class Curl implements AdapterInterface
{
    protected $host;
    protected $port;
    protected $curlHandler;
    protected $lastResponseInfo;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
        $this->curlHandler = curl_init();
        
        curl_setopt($this->curlHandler, CURLOPT_SSLVERSION, 1);
        //curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYPEER, true);
        //curl_setopt($this->curlHandler, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->curlHandler, CURLOPT_USERAGENT, "FHP-lib");
        curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandler, CURLOPT_URL, $this->host);
        curl_setopt($this->curlHandler, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($this->curlHandler, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curlHandler, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->curlHandler, CURLOPT_ENCODING, '');
        curl_setopt($this->curlHandler, CURLOPT_MAXREDIRS, 0);
        curl_setopt($this->curlHandler, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curlHandler, CURLOPT_HTTPHEADER, array("cache-control: no-cache", 'Content-Type: text/plain'));
    }

    public function send(AbstractMessage $message)
    {
        curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, base64_encode($message->toString()));
        $response = curl_exec($this->curlHandler);
        $this->lastResponseInfo = curl_getinfo($this->curlHandler);

        if (false === $response) {
            throw new CurlException('Failed connection to ' . $this->host, 0, null, $this->lastResponseInfo);
        }

        $statusCode = curl_getinfo($this->curlHandler, CURLINFO_HTTP_CODE);

        if ($statusCode < 200 || $statusCode > 299) {
            throw new CurlException('Bad response with status code ' . $statusCode, 0, null, $this->lastResponseInfo);
        }

        return base64_decode($response);
    }

    public function getLastResponseInfo()
    {
        return $this->lastResponseInfo;
    }
}
