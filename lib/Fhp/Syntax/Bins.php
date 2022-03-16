<?php

namespace Fhp\Syntax;

use Fhp\Segment\BaseDeg;

class Bins extends BaseDeg
{
    /** @var Bin[] @Max(99) */
    public $bins;

    /**
     * Gets the binary data.
     */
    public function getData(): array
    {
        return $this->bins;
    }
}
