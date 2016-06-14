<?php

namespace Fhp\DataElementGroups;

class SecurityDateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $dateTime = new \DateTime();
        $e = new SecurityDateTime(SecurityDateTime::DATETIME_TYPE_STS, $dateTime);

        $this->assertEquals('1:' . $dateTime->format('Ymd') . ':' . $dateTime->format('His'), (string) $e);
        $this->assertEquals('1:' . $dateTime->format('Ymd') . ':' . $dateTime->format('His'), $e->toString());
    }
}
