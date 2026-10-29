<?php

namespace Fhp\Tests\Unit\Segment;

use Fhp\Segment\Common\Kik;
use Fhp\Segment\Common\KtvV3;
use Fhp\Segment\WPD\HIWPDSv6;
use Fhp\Segment\WPD\HKWPDv6;
use Fhp\Syntax\Parser;
use PHPUnit\Framework\TestCase;

/**
 * Depotaufstellung in Version 6, wie sie die DKB in ihren Bankparametern meldet.
 */
class HKWPDTest extends TestCase
{
    public function testParametersOfVersion6()
    {
        /** @var HIWPDSv6 $hiwpds */
        $hiwpds = Parser::parseSegment("HIWPDS:60:6:3+1+1+0+N:N:N'", HIWPDSv6::class);
        $this->assertEquals(6, $hiwpds->getVersion());
        $this->assertFalse($hiwpds->getParameter()->getEingabeAnzahlEintraegeErlaubt());
        $this->assertFalse($hiwpds->getParameter()->getWaehrungDepotaufstellungWaehlbar());
        $this->assertFalse($hiwpds->getParameter()->getKursqualitaetWaehlbar());
    }

    public function testRequestOfVersion6()
    {
        $hkwpd = HKWPDv6::create(KtvV3::create('502022353', null, Kik::create('12030000')));
        $hkwpd->setSegmentNumber(3);
        $this->assertEquals("HKWPD:3:6+502022353::280:12030000'", $hkwpd->serialize());

        $hkwpd->setPaginationToken('TOKEN');
        $this->assertEquals("HKWPD:3:6+502022353::280:12030000++++TOKEN'", $hkwpd->serialize());
    }
}
