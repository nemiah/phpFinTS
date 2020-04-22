<?php

namespace Tests\Fhp;

use Fhp\Connection;
use Fhp\FinTs;
use Fhp\Options\Credentials;
use Fhp\Options\FinTsOptions;
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

    public function __construct(FinTsOptions $options, ?Credentials $credentials)
    {
        parent::__construct($options, $credentials);
    }

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
