<?php

namespace Fhp\Adapter;

use Fhp\Message\AbstractMessage;

class Debug implements AdapterInterface
{
    protected $host;
    protected $port;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function send(AbstractMessage $message)
    {
        return file_get_contents(__DIR__ . '/../../../develop/accounts_response.txt');
    }
}
