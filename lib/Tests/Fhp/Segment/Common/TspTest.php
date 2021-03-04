<?php

namespace Tests\Fhp\Segment\Common;

use Fhp\Segment\Common\Tsp;

class TspTest extends \PHPUnit\Framework\TestCase
{
    public function testParseWithoutTime()
    {
        $this->assertEquals(new \DateTime('2020-01-02T00:00:00'), Tsp::parse('20200102')->asDateTime());
        $this->assertEquals(new \DateTime('2020-07-02T00:00:00'), Tsp::parse('20200702')->asDateTime());
    }

    public function testParseWithTime()
    {
        $this->assertEquals(new \DateTime('2020-01-02T11:22:33'), Tsp::parse('20200102:112233')->asDateTime());
        $this->assertEquals(new \DateTime('2020-01-02T22:00:00'), Tsp::parse('20200102:220000')->asDateTime());
    }
}
