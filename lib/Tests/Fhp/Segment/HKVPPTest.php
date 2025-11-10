<?php

namespace Fhp\Segment;

use Fhp\Segment\VPP\HKVPPv1;
use Fhp\Syntax\Parser;
use PHPUnit\Framework\TestCase;

/**
 * Among other things, this test covers the serialization of arrays with @Max annotation.
 */
class HKVPPTest extends TestCase
{
    public function testSerialize()
    {
        $hkvpp = HKVPPv1::createEmpty();
        $hkvpp->setSegmentNumber(42);
        $hkvpp->unterstuetztePaymentStatusReports->paymentStatusReportDescriptor = ['A', 'B', 'C'];

        $serialized = $hkvpp->serialize();
        $this->assertEquals("HKVPP:42:1+A:B:C'", $serialized);

        /** @var HKVPPv1 $hkvpp */
        $hkvpp = Parser::parseSegment($serialized, HKVPPv1::class);
        $this->assertEquals(42, $hkvpp->getSegmentNumber());
        $this->assertEquals(['A', 'B', 'C'], $hkvpp->unterstuetztePaymentStatusReports->paymentStatusReportDescriptor);
    }
}
