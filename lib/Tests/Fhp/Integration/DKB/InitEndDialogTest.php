<?php

namespace Tests\Fhp\Integration\DKB;

class InitEndDialogTest extends DKBIntegrationTestBase
{
    // Dialog initialization with synchronization (HKSYN) and no TAN mode (instead 999) in order to request allowed TAN modes.
    const SYNC_WEAK_REQUEST = "HNHBK:1:3+000000000387+300+0+1'HNVSK:998:3+PIN:1+998+1+1::0+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@224@HNSHK:2:4+PIN:1+999+9999999+1+1+1::0+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HKIDN:3:2+280:12030000+test?@user+0+1'HKVVB:4:3+3+0+0+123456789ABCDEF0123456789+1.0'HKSYN:5:3+0'HNSHA:6:2+9999999++12345''HNHBS:7:1+1'";
    const SYNC_WEAK_RESPONSE = "HNHBK:1:3+000000000607+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HNVSK:998:3+PIN:1+998+1+2::0+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@382@HNSHK:2:4+PIN:1+999+9999999+1+1+2::0+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:5+0020::Auftrag ausgefuhrt.'HIRMS:5:2:4+3920::Zugelassene Zwei-Schritt-Verfahren fur den Benutzer.:921+0020::Der Auftrag wurde ausgefuhrt.'HISYN:6:4:5+FAKEKUNDENSYSTEMIDabcdefghij'HNSHA:7:2+6574344''HNHBS:8:1+1'";
    const SYNC_WEAK_END_REQUEST = "HNHBK:1:3+000000000415+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HNVSK:998:3+PIN:1+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@196@HNSHK:2:4+PIN:1+999+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HKEND:3:1+FAKEDIALOGIDabcdefghijklmnopqr'HNSHA:4:2+9999999++12345''HNHBS:5:1+2'";
    const SYNC_WEAK_END_RESPONSE = "HNHBK:1:3+000000000435+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:2'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@198@HNSHK:2:4+PIN:1+999+9999999+1+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+1+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HIRMG:3:2+0010::Nachricht entgegengenommen.+0100::Dialog beendet.'HNSHA:4:2+9999999''HNHBS:5:1+2'";

    // Strong-authenticated dialog with reference to HKTAB, using right TAN mode but fake TAN medium, then a HKTAB transaction, and dialog end.
    const HKTAB_INIT_REQUEST = "HNHBK:1:3+000000000474+300+0+1'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@284@HNSHK:2:4+PIN:2+921+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HKIDN:3:2+280:12030000+test?@user+FAKEKUNDENSYSTEMIDabcdefghij+1'HKVVB:4:3+3+0+0+123456789ABCDEF0123456789+1.0'HKTAN:5:6+4+HKTAB'HNSHA:6:2+9999999++12345''HNHBS:7:1+1'";
    const HKTAB_INIT_RESPONSE = "HNHBK:1:3+000000000681+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HNVSK:998:3+PIN:2+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@429@HNSHK:2:4+PIN:2+921+9999999+1+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:4+3920::Zugelassene Zwei-Schritt-Verfahren fur den Benutzer.:921+0020::Der Auftrag wurde ausgefuhrt.'HIRMS:5:2:5+3076::Starke Kundenauthentifizierung nicht notwendig.'HITAN:6:6:5+4++noref+nochallenge'HNSHA:7:2+9999999''HNHBS:8:1+1'";
    const HKTAB_REQUEST = "HNHBK:1:3+000000000388+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@169@HNSHK:2:4+PIN:2+921+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HKTAB:3:4+0+A'HNSHA:4:2+9999999++12345''HNHBS:5:1+2'";
    const HKTAB_RESPONSE = "HNHBK:1:3+000000000569+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:2'HNVSK:998:3+PIN:2+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@317@HNSHK:2:4+PIN:2+921+9999999+1+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0020::Der Auftrag wurde ausgefuhrt.'HITAB:5:4:3+0+A:1:::::::::::pushtan::::::::+A:1:::::::::::SomePhone1::::::::'HNSHA:6:2+9999999''HNHBS:7:1+2'";
    const HKTAB_END_REQUEST = "HNHBK:1:3+000000000415+300+FAKEDIALOGIDabcdefghijklmnopqr+3'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@196@HNSHK:2:4+PIN:2+921+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HKEND:3:1+FAKEDIALOGIDabcdefghijklmnopqr'HNSHA:4:2+9999999++12345''HNHBS:5:1+3'";
    const HKTAB_END_RESPONSE = "HNHBK:1:3+000000000466+300+FAKEDIALOGIDabcdefghijklmnopqr+3+FAKEDIALOGIDabcdefghijklmnopqr:3'HNVSK:998:3+PIN:2+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@214@HNSHK:2:4+PIN:2+921+9999999+1+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HIRMG:3:2+0010::Nachricht entgegengenommen.+0100::Dialog beendet.'HNSHA:4:2+9999999''HNHBS:5:1+3'";

    /**
     * @throws \Fhp\CurlException
     * @throws \Fhp\Protocol\ServerException
     */
    public function test_getTanModes_and_getTanMedia()
    {
        // When we request the TAN modes, it should first request the BPD anonymously.
        $this->expectMessage(static::ANONYMOUS_INIT_REQUEST, static::ANONYMOUS_INIT_RESPONSE);
        $this->expectMessage(static::ANONYMOUS_END_REQUEST, static::ANONYMOUS_END_RESPONSE);
        // And then it should use a personal dialog with Sicherheitsfunktion=999 to retrieve the allowed modes (3920).
        $this->expectMessage(static::SYNC_WEAK_REQUEST, static::SYNC_WEAK_RESPONSE);
        $this->expectMessage(static::SYNC_WEAK_END_REQUEST, static::SYNC_WEAK_END_RESPONSE);

        $tanModes = $this->fints->getTanModes();
        $this->assertArrayHasKey(921, $tanModes);
        $tanMode = $tanModes[921];
        $this->assertEquals('TAN2go', $tanMode->getName());
        $this->assertTrue($tanMode->needsTanMedium());

        // Now we want the TAN media for this TAN modes. That requires a separate dialog just for HKTAB.
        $this->expectMessage(static::HKTAB_INIT_REQUEST, static::HKTAB_INIT_RESPONSE);
        $this->expectMessage(static::HKTAB_REQUEST, static::HKTAB_RESPONSE);
        $this->expectMessage(static::HKTAB_END_REQUEST, static::HKTAB_END_RESPONSE);

        $tanMedia = $this->fints->getTanMedia(921);
        $this->assertCount(2, $tanMedia);
        $this->assertEquals('pushtan', $tanMedia[0]->getName());
        $this->assertEquals('SomePhone1', $tanMedia[1]->getName());

        $this->fints->selectTanMode($tanMode, 'SomePhone1');
    }

    /**
     * @throws \Throwable
     */
    public function test_init_and_end_dialog()
    {
        $this->initDialog();
        $this->assertNotNull($this->fints->getDialogId());
        $this->expectMessage(static::FINAL_END_REQUEST, static::FINAL_END_RESPONSE);
        $this->fints->endDialog();
    }
}
