<?php

namespace Fhp\Syntax;

use Fhp\Segment\BaseDeg;

class Bins extends BaseDeg
{
    /** @var Bin[] @Max(99) */
    public $bins;

    /**
     * Array of strings to store XML srings from the Bins structure.
     * @var string[]
     */
    private $xml;

    /**
     * Gets the binary data as array of strings.
     */
    public function getData(): array
    {
        foreach ($this->bins as $bin) {
            $xml[] = $bin->getData();
        }
        return $xml;
    }
}
