<?php

namespace Fhp\Segment\TAN;

use Fhp\Model\TanRequest;

interface HITAN extends TanRequest
{
    public const DUMMY_REFERENCE = 'noref';
    public const DUMMY_CHALLENGE = 'nochallenge';

    public function getTanProzess(): string;

    public function getAuftragsreferenz(): ?string;
}
