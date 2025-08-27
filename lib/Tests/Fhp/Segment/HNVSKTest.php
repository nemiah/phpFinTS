<?php

namespace Tests\Fhp\Segment;

use Fhp\Options\Credentials;
use Fhp\Options\FinTsOptions;
use Fhp\Segment\HNVSK\HNVSKv3;
use Fhp\Segment\HNVSK\SchluesselnameV3;
use PHPUnit\Framework\TestCase;

/**
 * Among other things, this test covers the serialization of Bin values.
 */
class HNVSKTest extends TestCase
{
    /**
     * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX1BJTlRBTl8yMDIwLTA3LTEwX2ZpbmFsX3ZlcnNpb24ucGRmIiwicGFnZSI6MTI3fQ.FJHEt1OwhZgDgfpwfO_ikZRn_hX8rbiSuesG2yyEle0/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
     * Section: F.2.2 a)
     */
    public const HBCI22_EXAMPLE = "HNVSK:998:3+PIN:1+998+1+1::2+1:20020610:102044+2:2:13:@8@00000000:5:1+280:10020030:12345:V:0:0+0'";

    public function testParse()
    {
        $hnvsk = HNVSKv3::parse(static::HBCI22_EXAMPLE);
        $this->assertEquals('00000000', $hnvsk->verschluesselungsalgorithmus->wertDesAlgorithmusparametersSchluessel->getData());
        $this->assertEquals('280', $hnvsk->schluesselname->kreditinstitutskennung->laenderkennzeichen);
        $this->assertEquals('10020030', $hnvsk->schluesselname->kreditinstitutskennung->kreditinstitutscode);
        $this->assertEquals('12345', $hnvsk->schluesselname->benutzerkennung);
        $this->assertEquals(SchluesselnameV3::CHIFFRIERSCHLUESSEL, $hnvsk->schluesselname->schluesselart);
    }

    public function testSerialize()
    {
        $options = new FinTsOptions();
        $options->bankCode = '10020030';
        $credentials = Credentials::create('12345', 'NOT USED');
        $hnvsk = HNVSKv3::create($options, $credentials, '2', null);
        $hnvsk->sicherheitsdatumUndUhrzeit->datum = '20020610';
        $hnvsk->sicherheitsdatumUndUhrzeit->uhrzeit = '102044';
        $this->assertEquals( // Replace binary zeros to make the diff readable in case the unit test fails.
            str_replace("\0", '0', static::HBCI22_EXAMPLE),
            str_replace("\0", '0', $hnvsk->serialize())
        );
    }
}
