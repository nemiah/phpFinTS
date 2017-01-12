<?php

namespace Fhp\ResponseTest;

use Fhp\Response\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected static function getMethod($class, $name)
    {
        $class  = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(TRUE);

        return $method;
    }

    public function test_getter_and_setter()
    {
        $response = self::getMethod('Fhp\Response\Response', 'splitSegment');

        $withoutEscape = new Response('');
        $escaped       = clone $withoutEscape;

        $segments = $response->invokeArgs($withoutEscape, [
            'HISAL:5:5:3+111111111::280:111111111+GiroBest+EUR+C:9999,99:EUR:20161018+C:0,:EUR:20161018+0,:EUR+9999,99:EUR',
        ]);

        $segmentsEscaped = $response->invokeArgs($escaped, [
            'HISAL:5:5:3+111111111::280:111111111+GiroBusiness?++EUR+C:9999,99:EUR:20161018+C:0,:EUR:20161018+0,:EUR+9999,99:EUR',
        ]);

        $this->assertEquals('HISAL:5:5:3', $segments[0]);
        $this->assertEquals('111111111::280:111111111', $segments[1]);
        $this->assertEquals('GiroBest', $segments[2]);
        $this->assertEquals('EUR', $segments[3]);
        $this->assertEquals('C:9999,99:EUR:20161018', $segments[4]);
        $this->assertEquals('C:0,:EUR:20161018', $segments[5]);
        $this->assertEquals('0,:EUR', $segments[6]);
        $this->assertEquals('9999,99:EUR', $segments[7]);

        $this->assertEquals('HISAL:5:5:3', $segmentsEscaped[0]);
        $this->assertEquals('111111111::280:111111111', $segmentsEscaped[1]);
        $this->assertEquals('GiroBusiness+', $segmentsEscaped[2]);
        $this->assertEquals('EUR', $segmentsEscaped[3]);
        $this->assertEquals('C:9999,99:EUR:20161018', $segmentsEscaped[4]);
        $this->assertEquals('C:0,:EUR:20161018', $segmentsEscaped[5]);
        $this->assertEquals('0,:EUR', $segmentsEscaped[6]);
        $this->assertEquals('9999,99:EUR', $segmentsEscaped[7]);
    }
}
