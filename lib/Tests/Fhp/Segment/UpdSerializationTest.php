<?php

namespace Fhp\Segment;

use Fhp\Segment\HIUPD\HIUPDv6;
use Fhp\Syntax\Parser;
use Fhp\Syntax\Serializer;
use PHPUnit\Framework\TestCase;

class UpdSerializationTest extends TestCase
{
    private const HIUPD = "HIUPD:7:6:4+PRIVAT::280:11223344+DE00112233440000000000+PRIVATE_______+1+EUR+PRIVATE__________________++Kontokorrent++HKSAK:1+HKISA:1+HKSSP:1+HKCAZ:1+HKEKA:1+HKKAU:1+HKCDB:1+HKPSP:1+HKCSL:1+HKCDL:1+HKPAE:1+HKDVK:1+HKPPD:1+HKCSA:1+HKCDN:1+HKBMB:1+HKBBS:1+HKDMB:1+HKDBS:1+HKCSB:1+HKCUB:1+HKKAA:1+HKPOF:1+HKQTG:1+HKSPA:1+HKDSB:1+HKIPZ:1+HKIPS:1+HKCUM:1+HKCCS:1+HKCDE:1+HKCSE:1+HKDSW:1+HKSAL:1+HKKIF:1+HKKAZ:1+HKAUB:1+GKVPU:1+GKVPD:1+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++{\"umsltzt\"?:\"2025-10-10-10.32.13.000636\"}'";

    public function testParserPlusSerializer(): void
    {
        /** @var HIUPDv6 $hiupd */
        $hiupd = Parser::parseSegment(static::HIUPD, HIUPDv6::class);
        $this->assertEquals(static::HIUPD, Serializer::serializeSegment($hiupd));
    }

    public function testNativePhpSerialization(): void
    {
        /** @var HIUPDv6 $hiupd */
        $hiupd = Parser::parseSegment(static::HIUPD, HIUPDv6::class);
        $before = $hiupd->getErlaubteGeschaeftsvorfaelle();

        /** @var HIUPDv6 $hiupd */
        $hiupd = unserialize(serialize($hiupd));
        $after = $hiupd->getErlaubteGeschaeftsvorfaelle();

        $this->assertEquals($before, $after);
    }
}
