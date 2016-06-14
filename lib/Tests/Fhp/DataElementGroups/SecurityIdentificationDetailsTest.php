<?php

namespace Fhp\DataElementGroups;

class SecurityIdentificationDetailsTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $e = new SecurityIdentificationDetails();
        $this->assertEquals('1::0', (string) $e);
        $this->assertEquals('1::0', $e->toString());

        $e = new SecurityIdentificationDetails(SecurityIdentificationDetails::PARTY_MS, 123);
        $this->assertEquals('1:1:123', (string) $e);
        $this->assertEquals('1:1:123', $e->toString());
    }
}
