<?php

namespace Tests\Fhp\Integration\Postbank;

class InitDialogWithBlockedPinTest extends PostbankIntegrationTestBase
{
    const SYNC_RESPONSE = "HNHBK:1:3+000000000858+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghijkl+1:20190102:030405+2:2:13:@8@\x00\x00\x00\x00\x00\x00\x00\x00:5:1+280:20010020:PRIVATE____:V:0:0+0'HNVSD:999:1+@602@HIRMG:2:2+0020::Dialoginitialisierung erfolgreich.+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:2+3931::Vorläufige Sperre liegt vor. Entsperren mit GV ?'PIN-Sperre aufheben?' möglich.'HIRMS:4:2:3+0020::Information fehlerfrei entgegengenommen.'HIRMS:5:2:4+3920::Meldung unterstützter Ein- und Zwei-Schritt-Verfahren:912:913:920:930'HIRMS:6:2:5+0030::Auftrag empfangen - Sicherheitsfreigabe erforderlich'HIRMS:7:2:6+0020::Auftrag ausgeführt.'HISYN:8:4:6+FAKEKUNDENSYSTEMIDabcdefghijkl'HITAN:9:6:5+4++51462SWZH3BBA20200128131606501+mobileTAN über Mobilfunknummer mT?:PRIVATE__+++mT?:PRIVATE__''HNHBS:10:1+1'";

    /**
     * @throws \Throwable
     */
    public function test_init_dialog_with_blocked_pin()
    {
        $this->markTestIncomplete('See https://github.com/nemiah/phpFinTS/issues/229');
        $this->initDialog();
    }
}
