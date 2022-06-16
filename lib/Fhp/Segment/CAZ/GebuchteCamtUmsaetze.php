<?php

namespace Fhp\Segment\CAZ;

use Fhp\Segment\BaseDeg;

/**
 * Needed for
 * Issue: GetStatementOfAccountXML receiving multiple CAMT XML files
 * @link: https://github.com/nemiah/phpFinTS/issues/370
 *
 * @link: https://github.com/nemiah/phpFinTS/pull/371
 */
class GebuchteCamtUmsaetze extends BaseDeg
{
    /** @var Bin[] @Max(299) */
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
