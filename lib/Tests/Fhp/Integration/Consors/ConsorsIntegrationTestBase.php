<?php

namespace Tests\Fhp\Integration\Consors;

use Fhp\Model\SEPAAccount;
use Tests\Fhp\FinTsPeer;
use Tests\Fhp\FinTsTestCase;

class ConsorsIntegrationTestBase extends FinTsTestCase
{
    const TEST_BANK_CODE = '76030080';
    const TEST_TAN_MODE = '900';

    // Anonymous dialog to fetch BPD (HKVVB, then HKEND).
    const ANONYMOUS_INIT_REQUEST = "HNHBK:1:3+000000000145+300+0+1'HKIDN:2:2+280:76030080+9999999999+0+0'HKVVB:3:3+0+0+0+123456789ABCDEF0123456789+1.0'HKTAN:4:6+4+HKIDN'HNHBS:5:1+1'";
    const ANONYMOUS_INIT_RESPONSE = "HNHBK:1:3+000000001418+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HIRMG:2:2:+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:2+0020::Anonymer Benutzer identifiziert.+3076::Keine starke Authentifizierung erforderlich.'HIRMS:4:2:3+0020::Informationen fehlerfrei entgegengenommen.+3050::BPD nicht mehr aktuell. Aktuelle Version folgt.'HIBPA:5:3:3+1+280:<PRIVAT>+Consors+0+1:2+201:210:220:300:400+100'HIKOM:6:4:3+280:<PRIVAT>+1+3:https?://brokerage-hbci.consorsbank.de/hbci::MIM:1'HISPAS:7:1:3+1+1+0+J:J:J:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.003.03:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.002.03:urn?:swift?:xsd?:\$pain.001.002.02:sepade.pain.001.001.02.xsd'HIKAZS:8:7:3+1+1+0+90:J:N'HISALS:9:3:3+1+1'HIKAZS:10:6:3+1+1+1+90:J:N'HICCSS:11:1:3+1+1+0'HIKAZS:12:5:3+1+1+90:J:N'HIKAZS:13:4:3+1+1+90:J'XIADAS:14:1:3+1+1'HIKAZS:15:3:3+1+1+90:J'HITANS:16:6:3+1+1+0+N:N:1:900:2:MS1.0.0:photoTAN::SecurePlus:8:1:Secure Plus TAN:999:J:1:N:0:0:N:J:00:0:J:1'HIWPDS:17:2:3+1+1+J'HIWPDS:18:6:3+1+1+0+J:J:J'HIWPDS:19:5:3+1+1+J:J:J'HIPROS:20:4:3+1+1+0'HIWPDS:21:4:3+1+1+J:J:J'HIPROS:22:3:3+1+1'HIWPDS:23:3:3+1+1+J'XIADSS:24:1:3+1+1'HISALS:25:4:3+1+1'HISALS:26:5:3+1+1'HISALS:27:6:3+1+1+0'HIPINS:28:1:3+1+1+0+:::::HKSPA:N:HKKAZ:J:HKSAL:J:HKCCS:J:XKADA:N:HKTAN:N:HKWPD:N:HKPRO:N:XKADS:N'HITAN:29:6:4+4++noref+nochallenge'HNHBS:30:1+1'";
    const ANONYMOUS_END_REQUEST = "HNHBK:1:3+000000000113+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HKEND:2:1+FAKEDIALOGIDabcdefghijklmnopqr'HNHBS:3:1+2'";
    const ANONYMOUS_END_RESPONSE = "HNHBK:1:3+000000000187+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:2'HIRMG:2:2:+0100::Der Dialog wurde beendet.'HIRMS:3:2:2+0020::Benutzer abgemeldet.'HNHBS:4:1+2'";

    // Dialog initialization with synchronization (HKSYN) and TAN mode 900 (which was either explicitly picked by the
    // user or is auto-picked because it's the only one anyway) in order to request allowed TAN modes.
    const SYNC_REQUEST = "HNHBK:1:3+000000000405+300+0+1'HNVSK:998:3+PIN:2+998+1+1::0+1:20190102:030405+2:2:13:@8@00000000:5:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@242@HNSHK:2:4+PIN:2+900+9999999+1+1+1::0+1+1:20190102:030405+1:999:1+6:10:19+280:76030080:test?@user:S:0:0'HKIDN:3:2+280:76030080+test?@user+0+1'HKVVB:4:3+1+0+0+123456789ABCDEF0123456789+1.0'HKTAN:5:6+4+HKIDN'HKSYN:6:3+0'HNSHA:7:2+9999999++12345''HNHBS:8:1+1'";
    const SYNC_RESPONSE = "HNHBK:1:3+000000000648+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HNVSK:998:3+PIN:2+998+1+2::0+1+2:2:13:@8@00000000:6:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@439@HIRMG:2:2:+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+0020::Angemeldet.+3076::Keine starke Authentifizierung erforderlich.+0901::PIN gultig.'HIRMS:4:2:4+0020::Informationen fehlerfrei entgegengenommen.+3920::Zugelassene Ein- und Zwei-Schritt-Verfahren fur den Benutzer:900'HIRMS:5:2:6+0020::Die Synchronisierung der Kundensystem-ID war erfolgreich.'HISYN:6:4:6+FAKEKUNDENSYSTEMIDabcdefghij'HITAN:7:6:5+4++noref+nochallenge''HNHBS:8:1+1'";
    const SYNC_END_REQUEST = "HNHBK:1:3+000000000415+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@196@HNSHK:2:4+PIN:2+900+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:76030080:test?@user:S:0:0'HKEND:3:1+FAKEDIALOGIDabcdefghijklmnopqr'HNSHA:4:2+9999999++12345''HNHBS:5:1+2'";
    const SYNC_END_RESPONSE = "HNHBK:1:3+000000000308+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:2'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+2:2:13:@8@00000000:6:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@73@HIRMG:2:2:+0100::Der Dialog wurde beendet.'HIRMS:3:2:3+0020::Abgemeldet.''HNHBS:4:1+2'";

    // Dialog initialization for main dialog (HKVVB). With Consorsbank, this requires entering a TAN.
    // The message numbers matter here, i.e. the ones at the very end of HBHBS (and HNHBK).
    // The final response contains the UPD because authentication was successful and the client UPD is outdated.
    const LOGIN_REQUEST = "HNHBK:1:3+000000000474+300+0+1'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@284@HNSHK:2:4+PIN:2+900+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:76030080:test?@user:S:0:0'HKIDN:3:2+280:76030080+test?@user+FAKEKUNDENSYSTEMIDabcdefghij+1'HKVVB:4:3+1+0+0+123456789ABCDEF0123456789+1.0'HKTAN:5:6+4+HKIDN'HNSHA:6:2+9999999++12345''HNHBS:7:1+1'";
    const LOGIN_RESPONSE = "HNHBK:1:3+000000000581+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HNVSK:998:3+PIN:2+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+2:2:13:@8@00000000:6:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@345@HIRMG:2:2:+0010::Die Nachricht wurde entgegengenommen.'HIRMS:3:2:3+0030::Auftrag empfangen - Sicherheitsfreigabe erforderlich.+0901::PIN gultig.'HIRMS:4:2:4+0020::Informationen fehlerfrei entgegengenommen.+3920::Zugelassene Ein- und Zwei-Schritt-Verfahren fur den Benutzer:900'HITAN:5:6:5+4++000003QS34CK6EMOUGT3JJOI834L7Kvb+Bitte TAN eingeben.''HNHBS:6:1+1'";
    const LOGIN_TAN_REQUEST = "HNHBK:1:3+000000000433+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@214@HNSHK:2:4+PIN:2+900+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:76030080:test?@user:S:0:0'HKTAN:3:6+2++++000003QS34CK6EMOUGT3JJOI834L7Kvb+N'HNSHA:4:2+9999999++12345:98765432''HNHBS:5:1+2'";
    const LOGIN_TAN_RESPONSE = "HNHBK:1:3+000000002153+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:2'HNVSK:998:3+PIN:2+998+1+2::FAKEKUNDENSYSTEMIDabcdefghij+1+2:2:13:@8@00000000:6:1+280:76030080:test?@user:V:0:0+0'HNVSD:999:1+@1915@HIRMG:2:2:+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+0020::Angemeldet.+3050::BPD nicht mehr aktuell. Aktuelle Version folgt.+3050::UPD nicht mehr aktuell. Aktuelle Version folgt.+3920::Zugelassene Ein- und Zwei-Schritt-Verfahren fur den Benutzer:900'HITAN:4:6:3+2++000003QS34CK6EMOUGT3JJOI834L7Kvb'HIBPA:5:3:3+1+280:<PRIVAT>+Consors+0+1:2+201:210:220:300:400+100'HIKOM:6:4:3+280:<PRIVAT>+1+3:https?://brokerage-hbci.consorsbank.de/hbci::MIM:1'HISPAS:7:1:3+1+1+0+J:J:J:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.003.03:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.002.03:urn?:swift?:xsd?:\$pain.001.002.02:sepade.pain.001.001.02.xsd'HIKAZS:8:7:3+1+1+0+90:J:N'HISALS:9:3:3+1+1'HIKAZS:10:6:3+1+1+1+90:J:N'HICCSS:11:1:3+1+1+0'HIKAZS:12:5:3+1+1+90:J:N'HIKAZS:13:4:3+1+1+90:J'XIADAS:14:1:3+1+1'HIKAZS:15:3:3+1+1+90:J'HITANS:16:6:3+1+1+0+N:N:1:900:2:MS1.0.0:photoTAN::SecurePlus:8:1:Secure Plus TAN:999:J:1:N:0:0:N:J:00:0:J:1'HIWPDS:17:2:3+1+1+J'HIWPDS:18:6:3+1+1+0+J:J:J'HIWPDS:19:5:3+1+1+J:J:J'HIPROS:20:4:3+1+1+0'HIWPDS:21:4:3+1+1+J:J:J'HIPROS:22:3:3+1+1'HIWPDS:23:3:3+1+1+J'XIADSS:24:1:3+1+1'HISALS:25:4:3+1+1'HISALS:26:5:3+1+1'HISALS:27:6:3+1+1+0'HIPINS:28:1:3+1+1+0+:::::HKSPA:N:HKKAZ:J:HKSAL:J:HKCCS:J:XKADA:N:HKTAN:N:HKWPD:N:HKPRO:N:XKADS:N'HIUPA:29:4:3+<PRIVATE___>+0+0+<PRIVATE>'HIUPD:30:6:3+012345678::280:<PRIVAT>+DE21<PRIVAT>0123456789+<PRIVATE___>++EUR+Max Musterma++Lohn/Gehalt/Rente Privat++HKCCS:1+HKKAZ:1+HKSPA:1+HKSAL:1+HKPRO:1'HIUPD:31:6:3+<PRIVATE>::280:<PRIVAT>+DE03<PRIVAT>0<PRIVATE>+<PRIVATE___>++EUR+Max Musterma++Kontokorrentkonto Privat++HKCCS:1+HKKAZ:1+HKSPA:1+HKSAL:1+HKPRO:1'HIUPD:32:6:3+987654321::280:<PRIVAT>+DE52<PRIVAT>0987654321+<PRIVATE___>++EUR+Max Musterma++Tagesgeldkonto++HKCCS:1+HKKAZ:1+HKSPA:1+HKSAL:1+HKPRO:1'HIUPD:33:6:3+987123456::280:<PRIVAT>++<PRIVATE___>++EUR+Max Musterma++Depot++HKWPD:1+HKPRO:1''HNHBS:34:1+2'";

    // Dialog end for main dialog (HKEND).
    const FINAL_END_REQUEST = "HKEND:3:1+FAKEDIALOGIDabcdefghijklmnopqr'";
    const FINAL_END_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.+0100::Dialog beendet.'";

    /**
     * Executes dialog synchronization and initialization, so that BPD and UPD are filled.
     * @throws \Throwable
     */
    protected function initDialog()
    {
        // We already know the TAN mode (900 below), so it will only fetch the BPD (anonymously) to verify it.
        $this->expectMessage(static::ANONYMOUS_INIT_REQUEST, static::ANONYMOUS_INIT_RESPONSE);
        $this->expectMessage(static::ANONYMOUS_END_REQUEST, static::ANONYMOUS_END_RESPONSE);

        // Then when we initialize a dialog, it's going to request a Kundensystem-ID and UPD.
        $this->expectMessage(static::SYNC_REQUEST, static::SYNC_RESPONSE);
        $this->expectMessage(static::SYNC_END_REQUEST, static::SYNC_END_RESPONSE);

        // And finally it can initialize the main dialog, but the bank wants a TAN.
        $this->expectMessage(static::LOGIN_REQUEST, static::LOGIN_RESPONSE);

        $this->fints->selectTanMode(900);
        $login = $this->fints->login();
        $login->maybeThrowError();
        $this->assertAllMessagesSeen();

        $this->assertTrue($login->needsTan(), 'Expected a TAN request, but got none.');
        $tanRequest = $login->getTanRequest();
        $this->assertNotNull($tanRequest);
        $this->assertEquals('000003QS34CK6EMOUGT3JJOI834L7Kvb', $tanRequest->getProcessId());
        $this->assertEquals('Bitte TAN eingeben.', $tanRequest->getChallenge());

        // Pretend that we close everything and open everything from scratch, as if it were a new PHP session.
        $persistedInstance = $this->fints->persist();
        $persistedLogin = serialize($login);
        $this->connection->expects($this->once())->method('disconnect');
        $this->fints = new FinTsPeer($this->options, $this->credentials, $persistedInstance);
        $this->fints->mockConnection = $this->setUpConnection();

        // Now provide the TAN.
        $this->expectMessage(static::LOGIN_TAN_REQUEST, static::LOGIN_TAN_RESPONSE);
        $login = unserialize($persistedLogin);
        $this->fints->submitTan($login, '98765432');
    }

    /**
     * @return SEPAAccount
     */
    protected function getTestAccount()
    {
        $sepaAccount = new SEPAAccount();
        $sepaAccount->setIban('DExxABCDEFGH1234567890');
        $sepaAccount->setBic('CSDBDE71XXX');
        $sepaAccount->setAccountNumber('1234567890');
        $sepaAccount->setBlz('50220500');
        return $sepaAccount;
    }
}
