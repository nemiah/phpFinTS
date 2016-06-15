<?php

namespace Fhp\DataElementGroups;

use Fhp\Deg;

/**
 * Class SecurityProfile.
 * @package Fhp\DataElementGroups
 */
class SecurityProfile extends Deg
{
    const PROFILE_PIN = 'PIN';
    const PROFILE_VERSION_1 = 1; // Ein-Schritt Tanverfahren
    const PROFILE_VERSION_2 = 2; // Zwei-Schritt Tanverfahren

    /**
     * SecurityProfile constructor.
     *
     * @param $securityProceduresCode
     * @param $securityProceduresVersion
     */
    public function __construct($securityProceduresCode, $securityProceduresVersion)
    {
        $this->addDataElement($securityProceduresCode);
        $this->addDataElement($securityProceduresVersion);
    }
}
