<?php

namespace Fhp\Adapter;

use Fhp\Message\AbstractMessage;

interface AdapterInterface
{
    /**
     * @param AbstractMessage $message
     * @return string
     */
    public function send(AbstractMessage $message);
}
