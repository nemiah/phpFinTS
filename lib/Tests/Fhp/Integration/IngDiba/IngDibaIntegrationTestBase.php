<?php

namespace Tests\Fhp\Integration\IngDiba;

use Fhp\Model\NoPsd2TanMode;
use Fhp\Model\SEPAAccount;
use Tests\Fhp\FinTsTestCase;

class IngDibaIntegrationTestBase extends FinTsTestCase
{
    const TEST_BANK_CODE = '50010517';
    const TEST_PIN = '123456';
    const TEST_KUNDENSYSTEM_ID = 'FAKEKUNDENSYSTEMIDabcdefghijkl';

    // NOTE: ING-DiBa supports neither anonymous dialogs (for BPD fetching) nor strong authentication (no HKTANv6).

    // Separate dialog for synchronization (HKSYN), response also contains the BPD since this is the first request.
    const SYNC_REQUEST = "HNHBK:1:3+000000000388+300+0+1'HNVSK:998:3+PIN:1+998+1+1::0+1:20190102:030405+2:2:13:@8@00000000:5:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@225@HNSHK:2:4+PIN:1+999+9999999+1+1+1::0+1+1:20190102:030405+1:999:1+6:10:19+280:50010517:test?@user:S:0:0'HKIDN:3:2+280:50010517+test?@user+0+1'HKVVB:4:3+0+0+0+123456789ABCDEF0123456789+1.0'HKSYN:5:3+0'HNSHA:6:2+9999999++123456''HNHBS:7:1+1'";
    const SYNC_RESPONSE = "HNHBK:1:3+000000001602+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HNVSK:998:3+PIN:1+998+1+2::0+1+2:2:13:@8@00000000:6:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@1391@HIRMG:2:2:+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+0020::Angemeldet.'HIRMS:4:2:4+0020::Informationen fehlerfrei entgegengenommen.+3050::BPD nicht mehr aktuell. Aktuelle Version folgt.+3920::Zugelassene Ein- und Zwei-Schritt-Verfahren fur den Benutzer:900'HIRMS:5:2:5+0020::Die Synchronisierung der Kundensystem-ID war erfolgreich.'HIBPA:6:3:4+7+280:50010517+ING-DiBa+0+1+220:300+200'HIKOM:7:4:4+280:50010517+1+3:https?://fints.ing-diba.de/fints/'HISPAS:8:1:4+1+1+0+J:J:J:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.003.03'HIPAES:9:1:4+1+1+0'HICCSS:10:1:4+1+1+0'HITANS:11:1:4+1+1+0+J:N:0:0:900:2:iTAN:iTAN:6:1:Index:3:1:N:N'HIKAZS:12:5:4+1+1+390:N:N'HICDNS:13:1:4+1+1+1+0:1:360:J:J:J:J:J:N:N:N:J:00:00::0'HICSBS:14:1:4+1+1+0+N:N'HICSAS:15:1:4+1+1+0+1:360'HIWPDS:16:6:4+1+1+0+N:N:N'HIWPDS:17:5:4+1+1+N:N:N'DIPAES:18:1:4+1+1'HICDLS:19:1:4+1+1+1+1:360:N:J'HIPROS:20:3:4+1+1'HICSES:21:1:4+1+1+0+1:360'HICSLS:22:1:4+1+1+0+J'HICDBS:23:1:4+1+1+0+N'HISALS:24:5:4+1+1'HICDES:25:1:4+1+1+1+4:1:360:00:00::0'DIPINS:26:1:4+1+1+HKSPA:N:HKPAE:J:HKCCS:J:HKTAN:N:HKKAZ:N:HKCDN:J:HKCSB:N:HKCSA:J:HKWPD:N:DKPAE:J:HKCDL:J:HKPRO:N:HKCSE:J:HKCSL:J:HKCDB:N:HKSAL:N:HKCDE:J'HIPINS:27:1:4+1+1+0+5:10:6:Kontonummer::HKSPA:N:HKPAE:J:HKCCS:J:HKTAN:N:HKKAZ:N:HKCDN:J:HKCSB:N:HKCSA:J:HKWPD:N:DKPAE:J:HKCDL:J:HKPRO:N:HKCSE:J:HKCSL:J:HKCDB:N:HKSAL:N:HKCDE:J'HISYN:28:4:5+FAKEKUNDENSYSTEMIDabcdefghijkl''HNHBS:29:1+1'";
    const SYNC_END_REQUEST = "HNHBK:1:3+000000000420+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HNVSK:998:3+PIN:1+998+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1:20190102:030405+2:2:13:@8@00000000:5:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@199@HNSHK:2:4+PIN:1+999+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1+1:20190102:030405+1:999:1+6:10:19+280:50010517:test?@user:S:0:0'HKEND:3:1+FAKEDIALOGIDabcdefghijklmnopqr'HNSHA:4:2+9999999++123456''HNHBS:5:1+2'";
    const SYNC_END_RESPONSE = "HNHBK:1:3+000000000319+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:2'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghijkl+1+2:2:13:@8@00000000:6:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@82@HIRMG:2:2:+0100::Der Dialog wurde beendet.'HIRMS:3:2:3+0020::Benutzer abgemeldet.''HNHBS:4:1+2'";

    // Dialog initialization for main dialog (HKVVB), which gives us the UPD too.
    const INIT_REQUEST = "HNHBK:1:3+000000000463+300+0+1'HNVSK:998:3+PIN:1+998+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1:20190102:030405+2:2:13:@8@00000000:5:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@271@HNSHK:2:4+PIN:1+999+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1+1:20190102:030405+1:999:1+6:10:19+280:50010517:test?@user:S:0:0'HKIDN:3:2+280:50010517+test?@user+FAKEKUNDENSYSTEMIDabcdefghijkl+1'HKVVB:4:3+7+0+0+123456789ABCDEF0123456789+1.0'HNSHA:5:2+9999999++123456''HNHBS:6:1+1'";
    const INIT_RESPONSE = "HNHBK:1:3+000000001018+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghijkl+1+2:2:13:@8@00000000:6:1+280:50010517:test?@user:V:0:0+0'HNVSD:999:1+@780@HIRMG:2:2:+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+0020::Angemeldet.'HIRMS:4:2:4+0020::Informationen fehlerfrei entgegengenommen.+3050::UPD nicht mehr aktuell. Aktuelle Version folgt.+3920::Zugelassene Ein- und Zwei-Schritt-Verfahren fur den Benutzer:900'HIUPA:5:4:4+test?@user+0+0+test?@user'HIUPD:6:6:4+test?@user::280:50010517+DE63500105171234567890+1234567890++EUR+NUTZER, NAME++Girokonto++HKCCS:1+HKCDB:1+HKCDE:1+HKCDL:1+HKCDN:1+HKCSA:1+HKCSB:1+HKCSE:1+HKCSL:1+HKDAE:1+HKKAZ:1+HKSAL:1+HKSPA:1+HKTUE:1+HKUEB:1+HKPRO:1+DKPAE:1+HKPAE:1+HKTAN:1'HIUPD:7:6:4+5575453216::280:50010517+DExxABCDEFGH1234567842+1234567842++EUR+NUTZER, NAME++Extra-Konto++HKCCS:1+HKCSA:1+HKCSB:1+HKCSE:1+HKCSL:1+HKKAZ:1+HKSAL:1+HKSPA:1+HKTUE:1+HKUEB:1+HKPRO:1+DKPAE:1+HKPAE:1+HKTAN:1''HNHBS:8:1+1'";

    // Dialog end for main dialog (HKEND).
    const FINAL_END_REQUEST = "HKEND:3:1+FAKEDIALOGIDabcdefghijklmnopqr'";
    const FINAL_END_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.+0100::Dialog beendet.'";

    /**
     * Executes dialog synchronization and initialization, so that BPD and UPD are filled.
     * @throws \Throwable
     */
    protected function initDialog()
    {
        // Then when we initialize a dialog, it's going to request a Kundensystem-ID and UPD.
        $this->expectMessage(static::SYNC_REQUEST, static::SYNC_RESPONSE);
        $this->expectMessage(static::SYNC_END_REQUEST, static::SYNC_END_RESPONSE);
        // And finally it can initialize the main dialog.
        $this->expectMessage(static::INIT_REQUEST, static::INIT_RESPONSE);

        $this->fints->selectTanMode(new NoPsd2TanMode());
        $login = $this->fints->login();
        $login->ensureSuccess();
        $this->assertAllMessagesSeen();
    }

    /**
     * @return SEPAAccount
     */
    protected function getTestAccount()
    {
        $sepaAccount = new SEPAAccount();
        $sepaAccount->setIban('DExxABCDEFGH1234567890');
        $sepaAccount->setBic('INGDDEFFXXX');
        $sepaAccount->setAccountNumber('1234567890');
        $sepaAccount->setBlz('50010517');
        return $sepaAccount;
    }
}
