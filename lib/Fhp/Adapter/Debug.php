<?php

namespace Fhp\Adapter;

use Fhp\Adapter\Exception\AdapterException;
use Fhp\Message\AbstractMessage;

/**
 * Class Debug Adapter.
 *
 * Use it to debug requests.
 *
 * @package Fhp\Adapter
 */
class Debug implements AdapterInterface
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
     * Debug constructor.
     *
     * @param $host
     * @param $port
     * @throws AdapterException
     */
    public function __construct($host, $port)
    {
        if (!is_integer($port) || (int) $port <= 0) {
            throw new AdapterException('Invalid port number');
        }

        $this->host = (string) $host;
        $this->port = (int) $port;
    }

    /**
     * Should return a dummy response body.
     *
     * @param AbstractMessage $message
     * @return string
     */
    public function send(AbstractMessage $message)
    {
        /* @todo Implement me
         * return file_get_contents(__DIR__ . '/../../../develop/accounts_response.txt');
         */
    }
}
