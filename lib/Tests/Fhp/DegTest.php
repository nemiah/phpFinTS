<?php

namespace Tests\Fhp;

use Fhp\Deg;

class DegTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_add_new_element()
    {
        $deg = new Deg();
        $deg->addDataElement('foobar');

        $this->assertEquals('foobar', $deg->toString());

        $deg->addDataElement('baz');
        $this->assertEquals('foobar:baz', $deg->toString());
        $this->assertEquals('foobar:baz', (string) $deg);
    }
}
