<?php

namespace Tests\Fhp\Model;

use Fhp\Segment\HIUPD\HIUPDv4;

class HIUPDTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
     * File: HBCI22 Final.pdf
     * Search for: "HIUPD:"
     */
    const HBCI22_EXAMPLES = array(
        // NOTE: These two examples are likely outdated in the document because the new $unterkontomerkmal field was
        // added. So it's `::280` and not just `:280`. The first of these two examples occurs twice in the document,
        // once in the correct format. Here both of them are "fixed" (hopefully).
        "HIUPD:16:4:4+1234567::280:10020030+12345+DEM+Ernst Müller++Giro Spezial+T:2000,:DEM+HKPRO:1+HKSAK:1+HKISA:1+HKSSP:1+HKUEB:1+HKLAS:1+HKKAN:1+HKKAZ:1+HKSAL:1'",
        "HIUPD:17:4:4+1234568::280:10020030+12345+DEM+Ernst Müller++Sparkonto 2000++HKPRO:1+HKSAK:0+HKISA:1+HKSSP:0+HKUEB:2:Z:1000,:DEM:7+HKKAN:1+HKKAZ:1+HKSAL:2'",
    );

    public function test_parse_HBCI22_example1()
    {
        /** @var HIUPDv4 $parsed */
        $parsed = HIUPDv4::parse(static::HBCI22_EXAMPLES[0]);
        $this->assertSame(16, $parsed->segmentkopf->segmentnummer);
        $this->assertSame(4, $parsed->segmentkopf->segmentversion);
        $this->assertSame('1234567', $parsed->kontoverbindung->kontonummer);
        $this->assertNull($parsed->kontoverbindung->unterkontomerkmal);
        $this->assertSame(280, $parsed->kontoverbindung->laenderkennzeichen);
        $this->assertSame('10020030', $parsed->kontoverbindung->kreditinstitutionscode);
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

    public function test_validate_HBCI22_example1()
    {
        /** @var HIUPDv4 $parsed */
        $parsed = HIUPDv4::parse(static::HBCI22_EXAMPLES[0]);
        $parsed->validate(); // Should not throw.
    }

    public function test_serialize_HBCI22_example1()
    {
        /** @var HIUPDv4 $parsed */
        $parsed = HIUPDv4::parse(static::HBCI22_EXAMPLES[0]);
        $this->assertEquals(static::HBCI22_EXAMPLES[0], $parsed->serialize());
    }

    public function test_parse_HBCI22_example2()
    {
        /** @var HIUPDv4 $parsed */
        $parsed = HIUPDv4::parse(static::HBCI22_EXAMPLES[1]);
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

    public function test_validate_HBCI22_example2()
    {
        $parsed = HIUPDv4::parse(static::HBCI22_EXAMPLES[1]);
        $parsed->validate(); // Should not throw.
    }

    public function test_serialize_HBCI22_example2()
    {
        $parsed = HIUPDv4::parse(static::HBCI22_EXAMPLES[1]);
        $this->assertEquals(static::HBCI22_EXAMPLES[1], $parsed->serialize());
    }
}
