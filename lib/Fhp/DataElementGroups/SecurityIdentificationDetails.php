<?php

namespace Fhp\DataElementGroups;

use Fhp\Deg;

/**
 * Class SecurityIdentificationDetails
 * @package Fhp\DataElementGroups
 */
class SecurityIdentificationDetails extends Deg
{
    const PARTY_MS = 1;   // sender
    const CID_NONE = '';

    /**
     * SecurityIdentificationDetails constructor.
     *
     * @param string $cid
     * @param int $systemId
     */
    public function __construct($cid = self::CID_NONE, $systemId = 0)
    {
        $this->addDataElement(static::PARTY_MS);
        $this->addDataElement($cid);
        $this->addDataElement($systemId);
    }
}
