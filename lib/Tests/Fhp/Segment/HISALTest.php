<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\Common\Kti;
use Fhp\Segment\Common\Sdo;
use Fhp\Segment\SAL\HISALv7;

class HISALTest extends \PHPUnit\Framework\TestCase
{
    const REAL_DKB_RESPONSE = "HITAB:1:4:3+0+A:1:::::::::::pushtan::::::::+A:1:::::::::::SomePhone1::::::::'";

    public function testEmptyKti()
    {
        $hisal = HISALv7::createEmpty();
        $hisal->segmentkopf->segmentnummer = 11;
        $hisal->kontoverbindungInternational = new Kti();  // Note that none of its fields are filled.
        $hisal->kontoproduktbezeichnung = 'Test';
        $hisal->kontowaehrung = 'EUR';
        $hisal->gebuchterSaldo = Sdo::create(42, 'EUR', \DateTime::createFromFormat('Ymd His', '20200102 030405'));

        $serialized = $hisal->serialize();
        $this->assertEquals("HISAL:11:7++Test+EUR+C:42,:EUR:20200102:030405'", $serialized);
        $parsed = HISALv7::parse($serialized);
        $this->assertEquals($parsed, $hisal);
    }
}
