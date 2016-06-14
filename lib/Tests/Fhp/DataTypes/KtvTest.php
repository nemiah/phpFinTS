<?php

namespace Fhp\DataTypes;

class KtvTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $d = new Ktv('123123123', 'sub', new Kik('DE', '72191600'));

        $this->assertEquals('123123123:sub:DE:72191600', (string) $d);
        $this->assertEquals('123123123:sub:DE:72191600', $d->toString());
    }
}
