<?php

namespace Fhp;

use Fhp\Adapter\AdapterInterface;
use Fhp\Message\AbstractMessage;

/**
 * Class Connection
 * @package Fhp
 */
class Connection
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Connection constructor.
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Uses the configured adapter to send a message.
     *
     * @param AbstractMessage $message
     * @return string
     */
    public function send(AbstractMessage $message)
    {
        return iconv('ISO-8859-1', 'UTF-8', $this->adapter->send($message));
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
