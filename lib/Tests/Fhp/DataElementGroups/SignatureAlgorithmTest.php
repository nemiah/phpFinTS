<?php

namespace Fhp\DataElementGroups;

class SignatureAlgorithmTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $e = new SignatureAlgorithm();
        $this->assertEquals('6:10:16', (string) $e);
        $this->assertEquals('6:10:16', $e->toString());
    }
}
