<?php

namespace Fhp;

/**
 * Thin wrapper around curl that does base64 encoding/decoding and converts errors to {@link CurlException}s.
 */
class Connection
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var resource
     */
    protected $curlHandle;

    /**
     * @var int
     */
    protected $timeoutConnect = 15;

    /**
     * @var int
     */
    protected $timeoutResponse = 30;

    /**
     * Connection constructor.
     *
     * @param string $host
     * @param int $timeoutConnect
     * @param int $timeoutResponse
     */
    public function __construct($host, $timeoutConnect = 15, $timeoutResponse = 30)
    {
        $this->host = $host;
        $this->timeoutConnect = $timeoutConnect;
        $this->timeoutResponse = $timeoutResponse;
    }

    private function connect()
    {
        $this->curlHandle = curl_init();

        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->curlHandle, CURLOPT_USERAGENT, 'phpFinTS');
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->host);
        curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT, $this->timeoutConnect);
        curl_setopt($this->curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curlHandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->curlHandle, CURLOPT_ENCODING, '');
        curl_setopt($this->curlHandle, CURLOPT_MAXREDIRS, 0);
        curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, $this->timeoutResponse);
        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, ['cache-control: no-cache', 'Content-Type: text/plain']);
    }

    /**
     * @param string $message The message to be sent, in HBCI/FinTS wire format.
     * @return string The response from the server, in HBCI/FinTS wire format.
     * @throws CurlException When the request fails.
     */
    public function send($message)
    {
        if (!$this->curlHandle) {
            $this->connect();
        }

        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, base64_encode($message));
        $response = curl_exec($this->curlHandle);

        if (false === $response) {
            throw new CurlException(
                'Failed connection to ' . $this->host . ': ' . curl_error($this->curlHandle),
                null,
                curl_errno($this->curlHandle),
                curl_getinfo($this->curlHandle)
            );
        }

        $statusCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        if ($statusCode < 200 || $statusCode > 299) {
            throw new CurlException(
                'Bad response with status code ' . $statusCode,
                $response,
                $statusCode,
                curl_getinfo($this->curlHandle)
            );
        }

        return base64_decode($response);
    }
}
