<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\SPA\HKSPAv2;
use PHPUnit\Framework\TestCase;

/**
 * Among other things, this test covers the serialization of Bin values.
 */
class HKSPATest extends TestCase
{
    public function testSerialize()
    {
        $hkspa = HKSPAv2::createEmpty();
        $hkspa->setSegmentNumber(42);
        $hkspa->kontoverbindung = [];
        $this->assertEquals("HKSPA:42:2'", $hkspa->serialize());
    }
}
