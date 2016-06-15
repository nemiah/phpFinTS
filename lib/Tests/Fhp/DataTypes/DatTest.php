<?php

namespace Fhp\DataTypes;

class DatTest extends \PHPUnit_Framework_TestCase
{
    public function test_to_string()
    {
        $dateTime = new \DateTime();
        $d = new Dat($dateTime);
        $this->assertEquals($dateTime->format('Ymd'), (string) $d);
        $this->assertEquals($dateTime->format('Ymd'), $d->toString());

        $dateTime2 = new \DateTime();
        $dateTime2->modify('+1 month');

        $d->setDate($dateTime2);
        $this->assertEquals($dateTime2->format('Ymd'), (string) $d);
        $this->assertEquals($dateTime2->format('Ymd'), $d->toString());

        $this->assertEquals($dateTime2, $d->getDate());
    }
}
