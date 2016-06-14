<?php

namespace Fhp\DataElementGroups;

class SecurityProfileTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $e = new SecurityProfile(SecurityProfile::PROFILE_PIN, SecurityProfile::PROFILE_VERSION_2);

        $this->assertEquals('PIN:2', (string) $e);
        $this->assertEquals('PIN:2', $e->toString());
    }
}
