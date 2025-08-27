<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\HIUPD\HIUPDv4;

class HIUPDTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
     * File: HBCI22 Final.pdf
     * Search for: "HIUPD:"
     */
    public const HBCI22_EXAMPLES = [
        // NOTE: These two examples are likely outdated in the document because the new $unterkontomerkmal field was
        // added. So it's `::280` and not just `:280`. The first of these two examples occurs twice in the document,
        // once in the correct format. Here both of them are "fixed" (hopefully).
        // NOTE: These examples are UTF-8 encoded in the source code, but the real wire format is ISO-8859-1 encoded,
        // so they need to be passed through mb_convert_encoding() before being used.
        "HIUPD:16:4:4+1234567::280:10020030+12345+DEM+Ernst Müller++Giro Spezial+T:2000,:DEM+HKPRO:1+HKSAK:1+HKISA:1+HKSSP:1+HKUEB:1+HKLAS:1+HKKAN:1+HKKAZ:1+HKSAL:1'",
        "HIUPD:17:4:4+1234568::280:10020030+12345+DEM+Ernst Müller++Sparkonto 2000++HKPRO:1+HKSAK:0+HKISA:1+HKSSP:0+HKUEB:2:Z:1000,:DEM:7+HKKAN:1+HKKAZ:1+HKSAL:2'",
    ];

    public function testParseHBCI22Example1()
    {
        $parsed = HIUPDv4::parse(mb_convert_encoding(static::HBCI22_EXAMPLES[0], 'ISO-8859-1', 'UTF-8'));
        $this->assertSame(16, $parsed->segmentkopf->segmentnummer);
        $this->assertSame(4, $parsed->segmentkopf->segmentversion);
        $this->assertSame('1234567', $parsed->kontoverbindung->kontonummer);
        $this->assertNull($parsed->kontoverbindung->unterkontomerkmal);
        $this->assertSame('280', $parsed->kontoverbindung->kik->laenderkennzeichen);
        $this->assertSame('10020030', $parsed->kontoverbindung->kik->kreditinstitutscode);
        $this->assertSame('12345', $parsed->kundenId);
        $this->assertSame('DEM', $parsed->kontowaehrung);
        $this->assertSame('Ernst Müller', $parsed->name1);
        $this->assertSame('Giro Spezial', $parsed->kontoproduktbezeichnung);

        $this->assertSame('T', $parsed->kontolimit->limitart);
        $this->assertSame(2000.0, $parsed->kontolimit->limitbetrag->wert);
        $this->assertSame('DEM', $parsed->kontolimit->limitbetrag->waehrung);
        $this->assertNull($parsed->kontolimit->limitTage);

        $this->assertCount(9, $parsed->erlaubteGeschaeftsvorfaelle);
    }

    public function testValidateHBCI22Example1()
    {
        $parsed = HIUPDv4::parse(mb_convert_encoding(static::HBCI22_EXAMPLES[0], 'ISO-8859-1', 'UTF-8'));
        $parsed->validate(); // Should not throw.
        $this->assertTrue(true);
    }

    public function testSerializeHBCI22Example1()
    {
        $parsed = HIUPDv4::parse(mb_convert_encoding(static::HBCI22_EXAMPLES[0], 'ISO-8859-1', 'UTF-8'));
        $this->assertEquals(mb_convert_encoding(static::HBCI22_EXAMPLES[0], 'ISO-8859-1', 'UTF-8'), $parsed->serialize());
    }

    public function testParseHBCI22Example2()
    {
        $parsed = HIUPDv4::parse(mb_convert_encoding(static::HBCI22_EXAMPLES[1], 'ISO-8859-1', 'UTF-8'));
        $this->assertSame('1234568', $parsed->kontoverbindung->kontonummer);
        $this->assertSame('Sparkonto 2000', $parsed->kontoproduktbezeichnung);
        $this->assertNull($parsed->kontolimit);

        $this->assertCount(8, $parsed->erlaubteGeschaeftsvorfaelle);
        $this->assertSame('HKUEB', $parsed->erlaubteGeschaeftsvorfaelle[4]->geschaeftsvorfall);
        $this->assertSame(2, $parsed->erlaubteGeschaeftsvorfaelle[4]->anzahlBenoetigterSignaturen);
        $this->assertSame('Z', $parsed->erlaubteGeschaeftsvorfaelle[4]->limitart);
        $this->assertSame(1000.0, $parsed->erlaubteGeschaeftsvorfaelle[4]->limitbetrag->wert);
        $this->assertSame('DEM', $parsed->erlaubteGeschaeftsvorfaelle[4]->limitbetrag->waehrung);
        $this->assertSame(7, $parsed->erlaubteGeschaeftsvorfaelle[4]->limitTage);
    }

    public function testValidateHBCI22Example2()
    {
        $parsed = HIUPDv4::parse(mb_convert_encoding(static::HBCI22_EXAMPLES[1], 'ISO-8859-1', 'UTF-8'));
        $parsed->validate(); // Should not throw.
        $this->assertTrue(true);
    }

    public function testSerializeHBCI22Example2()
    {
        $parsed = HIUPDv4::parse(mb_convert_encoding(static::HBCI22_EXAMPLES[1], 'ISO-8859-1', 'UTF-8'));
        $this->assertEquals(mb_convert_encoding(static::HBCI22_EXAMPLES[1], 'ISO-8859-1', 'UTF-8'), $parsed->serialize());
    }
}
