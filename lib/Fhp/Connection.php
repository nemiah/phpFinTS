<?php

namespace Fhp;

use Fhp\Adapter\AdapterInterface;
use Fhp\Message\AbstractMessage;

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
