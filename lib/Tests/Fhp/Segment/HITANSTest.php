<?php

namespace Tests\Fhp\Segment;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\TAN\HITANSv6;

class HITANSTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Real response from DKB (Deutsche Kreditbank).
     */
    const REAL_DKB_RESPONSE = [
        "HITANS:165:1:4+1+1+1+J:N:0:0:920:2:smsTAN:smsTAN:6:1:TAN-Nummer:3:1:J:J:900:2:iTAN:iTAN:6:1:TAN-Nummer:3:1:J:J'",
        "HITANS:166:3:4+1+1+1+J:N:0:910:2:HHD1.3.0:chipTAN manuell:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:1:911:2:HHD1.3.2OPT:chipTAN optisch:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:1:912:2:HHD1.3.2USB:chipTAN-USB:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:1:913:2:Q1S:chipTAN-QR:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:1:920:2:smsTAN:smsTAN:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:2:5:921:2:TAN2go:TAN2go:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:2:2:900:2:iTAN:iTAN:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:0'",
        "HITANS:167:6:4+1+1+1+J:N:0:910:2:HHD1.3.0:::chipTAN manuell:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:911:2:HHD1.3.2OPT:HHDOPT1:1.3.2:chipTAN optisch:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:912:2:HHD1.3.2USB:HHDUSB1:1.3.2:chipTAN-USB:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:913:2:Q1S:Secoder_UC:1.2.0:chipTAN-QR:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:920:2:smsTAN:::smsTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:5:921:2:TAN2go:::TAN2go:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:2:900:2:iTAN:::iTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:0'",
    ];

    public function test_parse_DKB_response_v6()
    {
        $parsed = HITANSv6::parse(static::REAL_DKB_RESPONSE[2]);
        $this->assertEquals(1, $parsed->maximaleAnzahlAuftraege);
        $parsedParams = $parsed->parameterZweiSchrittTanEinreichung;
        $this->assertEquals(true, $parsedParams->einschrittVerfahrenErlaubt);
        $this->assertCount(7, $parsedParams->verfahrensparameterZweiSchrittVerfahren);
        $this->assertEquals('HHD1.3.0', $parsedParams->verfahrensparameterZweiSchrittVerfahren[0]->technischeIdentifikationTanVerfahren);
        $this->assertEquals('chipTAN manuell', $parsedParams->verfahrensparameterZweiSchrittVerfahren[0]->nameDesZweiSchrittVerfahrens);
        $this->assertEquals('TAN2go', $parsedParams->verfahrensparameterZweiSchrittVerfahren[5]->technischeIdentifikationTanVerfahren);
        $this->assertEquals('iTAN', $parsedParams->verfahrensparameterZweiSchrittVerfahren[6]->technischeIdentifikationTanVerfahren);
        $this->assertEquals('00', $parsedParams->verfahrensparameterZweiSchrittVerfahren[6]->initialisierungsmodus);
    }

    public function test_segmentVersion_detection()
    {
        $this->assertEquals(1, BaseSegment::parse(static::REAL_DKB_RESPONSE[0])->getVersion());
        $this->assertEquals(3, BaseSegment::parse(static::REAL_DKB_RESPONSE[1])->getVersion());
        $this->assertEquals(HITANSv6::parse(static::REAL_DKB_RESPONSE[2]),
            BaseSegment::parse(static::REAL_DKB_RESPONSE[2]));
    }

    public function test_validate_invalid_segmentkopf()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('@Invalid int: LALA@');
        HITANSv6::parse("HITANS:LALA:1:4+1+1+1+J:N:0:0:920:2:smsTAN:smsTAN:6:1:TAN-Nummer:3:1:J:J'");
    }

    public function test_validate_invalid_deg()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('@Invalid bool: LALA@');
        HITANSv6::parse("HITANS:167:6:4+1+1+1+J:N:0:910:2:HHD1.3.0:::chipTAN manuell:6:1:TAN-Nummer:3:LALA:2:N:0:0:N:N:00:0:N:1'");
    }
}
