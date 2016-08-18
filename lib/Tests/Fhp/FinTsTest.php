<?php

namespace Tests\Fhp;

use Fhp\FinTs;

class FinTsTest extends \PHPUnit_Framework_TestCase
{

    public function escapeStringProvider()
    {
        return array(
            array('foo?@bar.de', 'foo@bar.de'),
            array('??pseudo?:pass?\'special?@', '?pseudo:pass\'special@'),
            array('nothingtodo', 'nothingtodo'),
            array('??', '?'),
            array('?:', ':'),
            array('?@', '@'),
            array('?\'', '\''),
            array('????', '??'),
            array('', ''),
            array('', null),
        );
    }

    /**
     * @dataProvider escapeStringProvider
     * @param string $expected
     * @param string $value
     */
    public function testEscapeString($expected, $value)
    {
        $fints = $this->getMockBuilder('\Fhp\FinTs')
            ->disableOriginalConstructor()
            ->getMock();

        $reflMethod = new \ReflectionMethod('\Fhp\FinTs', 'escapeString');
        $reflMethod->setAccessible(true);

        $this->assertSame($expected, $reflMethod->invoke($fints, $value));
    }
}
