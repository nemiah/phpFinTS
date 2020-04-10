<?php

namespace Tests\Fhp;

use Fhp\Connection;
use Fhp\FinTs;
use Fhp\Protocol\ServerException;

/**
 * Sub-classes {@link FinTs} to expose some of the protected functions, and also to inject the Connection mock.
 */
class FinTsPeer extends FinTs
{
    /**
     * @var Connection
     */
    public $mockConnection;

    /** {@inheritdoc} */
    protected function newConnection(): Connection
    {
        return $this->mockConnection;
    }

    /**
     * {@inheritdoc}
     * @throws ServerException
     */
    public function endDialog(bool $isAnonymous = false) // parent::endDialog() is protected
    {
        parent::endDialog($isAnonymous);
    }

    public function getDialogId()
    {
        return $this->dialogId;
    }
}
