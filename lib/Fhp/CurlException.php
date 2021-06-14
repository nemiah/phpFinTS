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
     * @var string|null
     */
    protected $curlMessage;

    public function __construct(string $message, ?string $response, int $code = 0, array $curlInfo = [], ?string $curlMessage = null)
    {
        parent::__construct($message, $code, null);
        $this->response = $response;
        $this->curlInfo = $curlInfo;
        $this->curlMessage = $curlMessage;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * Gets the curl info from request / response.
     */
    public function getCurlInfo(): array
    {
        return $this->curlInfo;
    }

    /**
     * Gets the curl message from request / response.
     */
    public function getCurlMessage(): ?string
    {
        return $this->curlMessage;
    }
}
