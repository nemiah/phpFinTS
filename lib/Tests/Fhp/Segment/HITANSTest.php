<?php

namespace Tests\Fhp\Model;

use Fhp\Segment\HITANS\HITANSv1;
use Fhp\Segment\HITANS\HITANSv3;

class HITANSTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Real response from DKB (Deutsche Kreditbank).
     */
    const REAL_DKB_RESPONSE = array(
        "HITANS:165:1:4+1+1+1+J:N:0:0:920:2:smsTAN:smsTAN:6:1:TAN-Nummer:3:1:J:J:900:2:iTAN:iTAN:6:1:TAN-Nummer:3:1:J:J'",
        "HITANS:166:3:4+1+1+1+J:N:0:910:2:HHD1.3.0:chipTAN manuell:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:1:911:2:HHD1.3.2OPT:chipTAN optisch:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:1:912:2:HHD1.3.2USB:chipTAN-USB:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:1:913:2:Q1S:chipTAN-QR:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:1:920:2:smsTAN:smsTAN:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:2:5:921:2:TAN2go:TAN2go:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:2:2:900:2:iTAN:iTAN:6:1:TAN-Nummer:3:1:J:2:0:N:N:N:00:0:0'",
        "HITANS:167:6:4+1+1+1+J:N:0:910:2:HHD1.3.0:::chipTAN manuell:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:911:2:HHD1.3.2OPT:HHDOPT1:1.3.2:chipTAN optisch:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:912:2:HHD1.3.2USB:HHDUSB1:1.3.2:chipTAN-USB:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:913:2:Q1S:Secoder_UC:1.2.0:chipTAN-QR:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:1:920:2:smsTAN:::smsTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:5:921:2:TAN2go:::TAN2go:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:2:N:2:900:2:iTAN:::iTAN:6:1:TAN-Nummer:3:J:2:N:0:0:N:N:00:0:N:0'",
    );

    public function test_parse_DKB_response_v1()
    {
        /** @var HITANSv1 $parsed */
        $parsed = HITANSv1::parse(static::REAL_DKB_RESPONSE[0]);

        $this->assertEquals(1, $parsed->maximaleAnzahlAuftraege);
        $this->assertEquals(1, $parsed->anzahlSignaturenMindestens);
        $this->assertEquals(1, $parsed->sicherheitsklasse);

        $parsedParams = $parsed->parameterZweiSchrittTanEinreichung;
        $this->assertEquals(true, $parsedParams->einschrittVerfahrenErlaubt);
        $this->assertEquals(false, $parsedParams->mehrAlsEinTanPflichtigerAuftragProNachrichtErlaubt);
        $this->assertEquals(0, $parsedParams->auftragsHashwertverfahren);
        $this->assertEquals(0, $parsedParams->sicherheitsprofilBankenSignatureBeiHitan);
        $this->assertCount(2, $parsedParams->verfahrensparameterZweiSchrittVerfahren);

        $verfahren1 = $parsedParams->verfahrensparameterZweiSchrittVerfahren[0];
        $this->assertEquals(920, $verfahren1->sicherheitsfunktion);
        $this->assertEquals("smsTAN", $verfahren1->technischeIdentifikationTanVerfahren);
        $this->assertEquals("TAN-Nummer", $verfahren1->textZurBelegungDesRueckgabewertes);
        $this->assertEquals(true, $verfahren1->mehrfachTanErlaubt);

        $verfahren1 = $parsedParams->verfahrensparameterZweiSchrittVerfahren[1];
        $this->assertEquals(900, $verfahren1->sicherheitsfunktion);
        $this->assertEquals("iTAN", $verfahren1->technischeIdentifikationTanVerfahren);
        $this->assertEquals("TAN-Nummer", $verfahren1->textZurBelegungDesRueckgabewertes);
        $this->assertEquals(true, $verfahren1->mehrfachTanErlaubt);
    }

    public function test_parse_DKB_response_v3_with_wrong_parser()
    {
        $this->expectException(\InvalidArgumentException::class);
        HITANSv1::parse(static::REAL_DKB_RESPONSE[1]);
    }

    public function test_validate_DKB_response_v1()
    {
        /** @var HITANSv1 $parsed */
        $parsed = HITANSv1::parse(static::REAL_DKB_RESPONSE[0]);
        $parsed->validate(); // Should not throw.
    }

    public function test_serialize_DKB_response_v1()
    {
        $parsed = HITANSv1::parse(static::REAL_DKB_RESPONSE[0]);
        $this->assertEquals(static::REAL_DKB_RESPONSE[0], $parsed->serialize());
    }

    public function test_parse_DKB_response_v3()
    {
        /** @var HITANSv3 $parsed */
        $parsed = HITANSv3::parse(static::REAL_DKB_RESPONSE[1]);
        $this->assertEquals(1, $parsed->maximaleAnzahlAuftraege);
        $parsedParams = $parsed->parameterZweiSchrittTanEinreichung;
        $this->assertEquals(true, $parsedParams->einschrittVerfahrenErlaubt);
        $this->assertCount(7, $parsedParams->verfahrensparameterZweiSchrittVerfahren);
        $this->assertEquals("HHD1.3.0", $parsedParams->verfahrensparameterZweiSchrittVerfahren[0]->technischeIdentifikationTanVerfahren);
        $this->assertEquals("chipTAN manuell", $parsedParams->verfahrensparameterZweiSchrittVerfahren[0]->nameDesZweiSchrittVerfahrens);
        $this->assertEquals("TAN2go", $parsedParams->verfahrensparameterZweiSchrittVerfahren[5]->technischeIdentifikationTanVerfahren);
        $this->assertEquals("iTAN", $parsedParams->verfahrensparameterZweiSchrittVerfahren[6]->technischeIdentifikationTanVerfahren);
        $this->assertEquals("00", $parsedParams->verfahrensparameterZweiSchrittVerfahren[6]->initialisierungsmodus);
    }

    public function test_validate_DKB_response_v3()
    {
        $parsed = HITANSv3::parse(static::REAL_DKB_RESPONSE[1]);
        $parsed->validate(); // Should not throw.
    }

    public function test_serialize_DKB_response_v3()
    {
        $parsed = HITANSv3::parse(static::REAL_DKB_RESPONSE[1]);
        $this->assertEquals(static::REAL_DKB_RESPONSE[1], $parsed->serialize());
    }
}
