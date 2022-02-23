<?php

namespace Tests\Fhp\Integration\Consors;

use Fhp\CurlException;
use Fhp\Protocol\ServerException;

class InitEndDialogTest extends ConsorsIntegrationTestBase
{
    // Dialog initialization with synchronization (HKSYN) and no TAN mode (instead 999) in order to request allowed TAN modes.
    public const SYNC_WEAK_REQUEST = "HNHBK:1:3+000000000387+300+0+1'HNVSK:998:3+PIN:1+998+1+1::0+1:20190102:030405+2:2:13:@8@00000000:5:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@224@HNSHK:2:4+PIN:1+999+9999999+1+1+1::0+1+1:20190102:030405+1:999:1+6:10:19+280:76030080:test?@user:S:0:0'HKIDN:3:2+280:76030080+test?@user+0+1'HKVVB:4:3+1+0+0+123456789ABCDEF0123456789+1.0'HKSYN:5:3+0'HNSHA:6:2+9999999++12345''HNHBS:7:1+1'";
    public const SYNC_WEAK_RESPONSE = "HNHBK:1:3+000000000561+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HNVSK:998:3+PIN:1+998+1+2::0+1+2:2:13:@8@00000000:6:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@352@HIRMG:2:2:+0010::Die Nachricht wurde entgegengenommen.'HIRMS:3:2:3+0020::Angemeldet.+0901::PIN gultig.'HIRMS:4:2:4+0020::Informationen fehlerfrei entgegengenommen.+3920::Zugelassene Ein- und Zwei-Schritt-Verfahren fur den Benutzer:900'HIRMS:5:2:5+0020::Die Synchronisierung der Kundensystem-ID war erfolgreich.'HISYN:6:4:5+FAKEKUNDENSYSTEMIDabcdefghij''HNHBS:7:1+1'";
    public const SYNC_WEAK_END_REQUEST = "HNHBK:1:3+000000000415+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HNVSK:998:3+PIN:1+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@196@HNSHK:2:4+PIN:1+999+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:76030080:test?@user:S:0:0'HKEND:3:1+FAKEDIALOGIDabcdefghijklmnopqr'HNSHA:4:2+9999999++12345''HNHBS:5:1+2'";
    public const SYNC_WEAK_END_RESPONSE = "HNHBK:1:3+000000000308+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:2'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+2:2:13:@8@00000000:6:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@73@HIRMG:2:2:+0100::Der Dialog wurde beendet.'HIRMS:3:2:3+0020::Abgemeldet.''HNHBS:4:1+2'";

    /**
     * @throws CurlException
     * @throws ServerException
     */
    public function testGetTanModesAndGetTanMedia()
    {
        // When we request the TAN modes, it should first request the BPD anonymously.
        $this->expectMessage(static::ANONYMOUS_INIT_REQUEST, static::ANONYMOUS_INIT_RESPONSE);
        $this->expectMessage(static::ANONYMOUS_END_REQUEST, static::ANONYMOUS_END_RESPONSE);
        // And then it should use a personal dialog with Sicherheitsfunktion=999 to retrieve the allowed modes (3920).
        $this->expectMessage(static::SYNC_WEAK_REQUEST, static::SYNC_WEAK_RESPONSE);
        $this->expectMessage(static::SYNC_WEAK_END_REQUEST, static::SYNC_WEAK_END_RESPONSE);

        $tanModes = $this->fints->getTanModes();
        $this->assertAllMessagesSeen();

        $this->assertArrayHasKey(900, $tanModes);
        $tanMode = $tanModes[900];
        $this->assertEquals('SecurePlus', $tanMode->getName());
        $this->assertFalse($tanMode->needsTanMedium());
        $this->fints->selectTanMode($tanMode);
    }

    /**
     * @throws \Throwable
     */
    public function testInitAndEndDialog()
    {
        $this->initDialog();
        $this->assertNotNull($this->fints->getDialogId());
        $this->expectMessage(static::FINAL_END_REQUEST, static::FINAL_END_RESPONSE);
        $this->fints->endDialog();
    }
}
