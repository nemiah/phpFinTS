<?php

namespace Fhp\Adapter;

use Fhp\Message\AbstractMessage;
use Fhp\Message\Message;

class Stream implements AdapterInterface
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
        $context = stream_context_create();
        $fp = stream_socket_client($this->host . ":" . $this->port, $errno, $errstr, 60, STREAM_CLIENT_CONNECT, $context);
        stream_set_timeout($fp, 60);

        if (!$fp) {
            throw new \Exception("socket_error", $errstr);
        }

        fwrite($fp, (string) $message);

        return stream_get_contents($fp);
    }
}
