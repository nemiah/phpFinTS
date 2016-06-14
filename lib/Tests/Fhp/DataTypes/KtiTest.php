<?php

namespace Fhp\DataTypes;

class KtiTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $d = new Kti('someiban', 'somebic', 'someaccountNumber', 'sub', new Kik('DE', '72191600'));

        $this->assertEquals('someiban:somebic:someaccountNumber:sub:DE:72191600', (string) $d);
        $this->assertEquals('someiban:somebic:someaccountNumber:sub:DE:72191600', $d->toString());
    }
}
