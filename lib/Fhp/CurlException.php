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

    public function __construct(string $message, ?string $response, int $code = 0, array $curlInfo = [])
    {
        parent::__construct($message, $code, null);
        $this->response = $response;
        $this->curlInfo = $curlInfo;
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
}
