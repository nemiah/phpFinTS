<?php

namespace Tests\Fhp\Protocol;

use Fhp\Options\Credentials;
use Fhp\Options\FinTsOptions;
use Fhp\Protocol\DialogInitialization;

class DialogInitializationTestModel extends DialogInitialization
{
    public function __construct(string $kundensystemId, string $needTanForSegment)
    {
        parent::__construct(
            new FinTsOptions(),
            Credentials::create('user', 'password'),
            null,
            '',
            $kundensystemId,
            null,
        );

        $this->needTanForSegment = $needTanForSegment;
    }

    public function needsTan(): bool
    {
        return true;
    }
}
