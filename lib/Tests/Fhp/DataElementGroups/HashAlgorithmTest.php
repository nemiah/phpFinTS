<?php

namespace Tests\Fhp\DataElementGroups;

use Fhp\DataElementGroups\HashAlgorithm;

class HashAlgorithmTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $e = new HashAlgorithm();
        $this->assertEquals('1:999:1', (string) $e);
        $this->assertEquals('1:999:1', $e->toString());
    }
}
