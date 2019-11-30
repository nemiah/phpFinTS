<?php

namespace Tests\Fhp;

use Fhp\Connection;
use Fhp\FinTsNew;
use Fhp\Protocol\ServerException;

/**
 * Sub-classes {@link FinTsNew} to expose some of the protected functions, and also to inject the Connection mock.
 */
class FinTsPeer extends FinTsNew
{
    /**
     * @var Connection
     */
    public $mockConnection;

    protected function newConnection()
    {
        return $this->mockConnection;
    }

    /**
     * {@inheritdoc}
     * @throws ServerException
     */
    public function endDialog($isAnonymous = false) // parent::endDialog() is protected
    {
        parent::endDialog($isAnonymous);
    }

    public function getDialogId()
    {
        return $this->dialogId;
    }
}
