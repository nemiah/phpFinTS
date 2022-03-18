<?php

namespace Fhp\Segment\CAZ;

use Fhp\Segment\BaseDeg;

class GebuchteCamtUmsaetze extends BaseDeg
{
    /** @var Bin[] @Max(99) */
    public $gebuchteCamtUmsaetze;

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
        $xml = [];
        foreach ($this->gebuchteCamtUmsaetze as $bin) {
            $xml[] = $bin->getData();
        }
        return $xml;
    }
}
