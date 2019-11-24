<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\TAB\HITABv4;

class HITABTest extends \PHPUnit\Framework\TestCase
{
    const REAL_DKB_RESPONSE = "HITAB:1:4:3+0+A:1:::::::::::pushtan::::::::+A:1:::::::::::SomePhone1::::::::'";

    public function test_parse_DKB()
    {
        // NOTE: Among the many colons, the response contains a KtvV3 and a Kti nested inside TanMediumListeV4. Each is
        // a DEG nested inside another DEG. In that case, if the nested DEG is absent (null), we still need all the
        // empty fields of the inside of the absent DEG in order not to mess up the field offsets for subsequent fields.
        // This is different from DEGs nested in segments because the segment delimiters are different and we would just
        // see two delimiters "++" with nothing in between for an absent DEG.
        $parsed = HITABv4::parse(static::REAL_DKB_RESPONSE);
        $liste = $parsed->getTanMediumListe();
        $this->assertCount(2, $liste);
        $this->assertEquals('pushtan', $liste[0]->getName());
        $this->assertEquals('SomePhone1', $liste[1]->getName());
    }

    public function test_serialize()
    {
        // NOTE: Our serializer produces fewer redundant colons, but after parsing it again, it should be the same.
        $parsed = HITABv4::parse(static::REAL_DKB_RESPONSE);
        $serialized = $parsed->serialize();
        $this->assertEquals("HITAB:1:4:3+0+A:1:::::::::::pushtan+A:1:::::::::::SomePhone1'", $serialized);
        $reparsed = HITABv4::parse($serialized);
        $this->assertEquals($reparsed, $parsed);
    }
}
