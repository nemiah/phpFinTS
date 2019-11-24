<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\HIUPA\HIUPAv4;

class HIUPATest extends \PHPUnit\Framework\TestCase
{
    const TEST_HIUPA = "HIUPA:6:4:4+4567890123+0+0++PERSNR00101231234123'";
    const PARTIAL_HIUPD = "HIUPD:7:6:4+1234567890::280:'";

    public function test_parse_broken()
    {
        $this->expectException(\InvalidArgumentException::class);
        HIUPAv4::parse(static::TEST_HIUPA.static::PARTIAL_HIUPD);
    }
}
