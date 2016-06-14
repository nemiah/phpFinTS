<?php

namespace Fhp\DataTypes;

class KikTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $d = new Kik('DE', '72191600');
        $this->assertEquals('DE:72191600', (string) $d);
        $this->assertEquals('DE:72191600', $d->toString());
    }
}
