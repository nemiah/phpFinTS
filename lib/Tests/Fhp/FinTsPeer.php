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
    public static ?Connection $mockConnection = null;

    public function __construct(FinTsOptions $options, ?Credentials $credentials)
    {
        parent::__construct($options, $credentials);
    }

    protected function newConnection(): Connection
    {
        return self::$mockConnection;
    }

    /**
     * @throws ServerException
     */
    public function endDialog(bool $isAnonymous = false): void // parent::endDialog() is protected
    {
        parent::endDialog($isAnonymous);
    }

    public function getDialogId(): ?string
    {
        return $this->dialogId;
    }
}
