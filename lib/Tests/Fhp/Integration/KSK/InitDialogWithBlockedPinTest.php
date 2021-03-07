<?php

namespace Tests\Fhp\Integration\KSK;

use Fhp\Protocol\ServerException;

class InitDialogWithBlockedPinTest extends KSKIntegrationTestBase
{
    // Note that this overrides the SYNC_RESPONSE in the super class.
    // This is what the bank sends when the PIN was wrong, note the 3938 warning.
    const INIT_RESPONSE = "HNHBK:1:3+000000000762+300+443664330360=877047716332BL8I=+1+443664330360=877047716332BL8I=:1'HNVSK:998:3+PIN:2+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1:20200527:224900+2:2:13:@8@00000000:5:1+280:71152570:test?@user:V:0:0+0'HNVSD:999:1+@510@HNSHK:2:4+PIN:2+910+9999999+1+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20200527:224900+1:999:1+6:10:19+280:71152570:test?@user:S:0:0'HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:4+3938::Ihr Zugang ist vorlaufig gesperrt - Bitte PIN-Sperre aufheben.+3920::Zugelassene Zwei-Schritt-Verfahren fur den Benutzer.:910:911:912:913+0020::Der Auftrag wurde ausgefuhrt.'HIRMS:5:2:5+3076::Starke Kundenauthentifizierung nicht notwendig.'HITAN:6:6:5+4++noref+nochallenge'HNSHA:7:2+9999999''HNHBS:8:1+1'";

    /**
     * @throws \Throwable
     */
    public function testInitDialogWithBlockedPin()
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionMessageMatches('/Ihr Zugang ist vorlaufig gesperrt/');
        $this->initDialog();
    }

    protected function tearDown(): void
    {
        // After the dialog was aborted due to invalid PIN, the usual dialog initialization messages won't happen anymore.
        $this->expectedMessages = [];
        parent::tearDown();
    }
}
