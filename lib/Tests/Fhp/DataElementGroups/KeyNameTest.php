<?php

namespace Fhp\DataElementGroups;

use Fhp\DataTypes\Kik;
use Fhp\Deg;

class KeyNameTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $e = new KeyName('DE', '72191600', 'username');
        $this->assertEquals('DE:72191600:username:V:0:0', (string) $e);
        $this->assertEquals('DE:72191600:username:V:0:0', $e->toString());
    }
}
