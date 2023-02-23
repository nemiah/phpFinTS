<?php

namespace Tests\Fhp\Integration\Postbank;

use Fhp\Model\SEPAAccount;
use Tests\Fhp\FinTsTestCase;

class PostbankIntegrationTestBase extends FinTsTestCase
{
    public const TEST_BANK_CODE = '20010020';
    public const TEST_TAN_MODE = 930;
    public const TEST_USERNAME = 'PRIVATE____';
    public const TEST_PIN = 'PRIVATE___';

    // Anonymous dialog to fetch BPD (HKVVB, then HKEND).
    public const ANONYMOUS_INIT_REQUEST = "HNHBK:1:3+000000000145+300+0+1'HKIDN:2:2+280:20010020+9999999999+0+0'HKVVB:3:3+0+0+0+123456789ABCDEF0123456789+1.0'HKTAN:4:6+4+HKIDN'HNHBS:5:1+1'";
    public const ANONYMOUS_INIT_RESPONSE = "HNHBK:1:3+000000002779+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HIRMG:2:2+0020::Dialoginitialisierung erfolgreich.+0010::Nachricht entgegengenommen.'HIRMS:3:2:2+0020::Information fehlerfrei entgegengenommen.'HIRMS:4:2:3+1040::BPD nicht mehr aktuell. Aktuelle Version folgt.+1050::UPD nicht mehr aktuell. Aktuelle Version folgt.'HIBPA:5:3:3+14+280:10010010+Postbank -Giro- Hamburg+0+1+300+9999'HIKOM:6:4:3+280:10010010+1+3:https?://hbci.postbank.de/banking/hbci.do::MIM:1'HIPINS:7:1:3+1+1+0+5:50:6:::HKPSA:J:HKPRO:N:HKEKA:N:HKSPA:N:HKKAU:N:HKKAZ:N:HKCDN:J:HKDMC:J:DKPSA:J:HKDME:J:HKCSB:N:HKCSE:J:HKTAB:N:HKCDE:J:HKCSL:J:HKCME:J:HKTAN:N:HKSAL:N:HKCDL:J:HKCML:J:HKCDB:N:HKDSC:J:HKBME:J:HKCCM:J:DKPAE:J:HKCMB:N:HKPAE:J:HKCCS:J'DIPINS:8:1:3+1+1+HKPRO:N:HKEKA:N:HKSPA:N:HKKAU:N:HKKAZ:N:HKCDN:J:HKDMC:J:DKPSA:J:HKDME:J:HKCSB:N:HKCSE:J:HKTAB:N:HKCDE:J:HKCSL:J:HKCME:J:HKTAN:N:HKSAL:N:HKCDL:J:HKCML:J:HKCDB:N:HKDSC:J:HKBME:J:HKCCM:J:DKPAE:J:HKCMB:N:HKCCS:J'HIPAES:9:1:3+1+1+0'DIPAES:10:1:3+1+1'HIPSAS:11:1:3+1+1+0'DIPSAS:12:1:3+1+1'HITANS:13:6:3+1+1+0+N:N:0:910:2:HHD1.3.2OPT:HHDOPT1:1.3.2:chipTAN optisch HHD1.3.2:6:1:Challenge:999:N:1:N:0:2:N:J:00:2:N:9:911:2:HHD1.3.2:HHD:1.3.2:chipTAN manuell HHD1.3.2:6:1:Challenge:999:N:1:N:0:2:N:J:00:2:N:9:912:2:HHD1.4OPT:HHDOPT1:1.4:chipTAN optisch HHD1.4:6:1:Challenge:999:N:1:N:0:2:N:J:00:2:N:9:913:2:HHD1.4:HHD:1.4:chipTAN manuell HHD1.4:6:1:Challenge:999:N:1:N:0:2:N:J:00:2:N:9:920:2:BestSign:BestSign::BestSign:6:2:BestSign:999:N:1:N:0:2:N:J:00:2:N:9:930:2:mobileTAN:mobileTAN::mobileTAN:6:2:mobileTAN:999:N:1:N:0:2:N:J:00:2:N:9'HITABS:14:2:3+1+1+0'HITABS:15:4:3+1+1+0'HIPROS:16:3:3+1+1'HISPAS:17:1:3+1+1+0+J:N:J:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.003.03:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.008.003.02:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.008.001.02'HIKAZS:18:5:3+1+1+90:N:N'HIKAZS:19:6:3+1+1+0+90:N:N'HISALS:20:5:3+1+1'HISALS:21:6:3+1+1+0'HIEKAS:22:3:3+1+1+0+J:N:N:3'HIKAUS:23:1:3+1+1+0'HICCSS:24:1:3+1+1+0'HICSES:25:1:3+1+1+0+0:180'HICSBS:26:1:3+1+1+1+N:J'HICSLS:27:1:3+1+1+1+J'HICDES:28:1:3+1+1+1+4:1:180:00:00:00:12345'HICDBS:29:1:3+1+1+1+N'HICDNS:30:1:3+1+1+1+0:1:180:J:J:J:J:J:J:J:J:J:00:00:00:12345'HICDLS:31:1:3+1+1+1+1:1:N:J'HICCMS:32:1:3+1+1+0+1000:J:J'HICMES:33:1:3+1+1+0+1:180:1000:J:J'HICMBS:34:1:3+1+1+1+N:J'HICMLS:35:1:3+1+1+1'HIDMES:36:1:3+1+1+0+1:30:1:30:1000:J:J'HIBMES:37:1:3+1+1+0+1:30:1:30:1000:J:J'HIDSCS:38:1:3+1+1+1+1:30:1:30::urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.008.003.02'HIDMCS:39:1:3+1+1+1+1000:J:J:1:30:1:30::urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.008.003.02'HIUPA:40:4:3+9999999999+1+0'HIUPD:41:6:3+9999999999::280:10010010++9999999999+++anonym'HITAN:42:6:4+4++noref+nochallenge'HNHBS:43:1+1'";
    public const ANONYMOUS_END_REQUEST = "HNHBK:1:3+000000000113+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HKEND:2:1+FAKEDIALOGIDabcdefghijklmnopqr'HNHBS:3:1+2'";
    public const ANONYMOUS_END_RESPONSE = "HNHBK:1:3+000000000183+300+FAKEDIALOGIDabcdefghijklmnopqr+2+FAKEDIALOGIDabcdefghijklmnopqr:2'HIRMG:2:2+0010::Nachricht entgegengenommen.'HIRMS:3:2:2+0100::Dialog beendet.'HNHBS:4:1+2'";

    // Separate dialog for synchronization (HKSYN), response does not contain BPD again.
    public const SYNC_REQUEST = "HNHBK:1:3+000000000396+300+0+1'HNVSK:998:3+PIN:1+998+1+1::0+1:20190102:030405+2:2:13:@8@00000000:5:1+280:20010020:PRIVATE____:V:0:0+0'HNVSD:999:1+@232@HNSHK:2:4+PIN:1+999+9999999+1+1+1::0+1+1:20190102:030405+1:999:1+6:10:19+280:20010020:PRIVATE____:S:0:0'HKIDN:3:2+280:20010020+PRIVATE____+0+1'HKVVB:4:3+14+0+0+123456789ABCDEF0123456789+1.0'HKSYN:5:3+0'HNSHA:6:2+9999999++PRIVATE___''HNHBS:7:1+1'";
    public const SYNC_RESPONSE = "HNHBK:1:3+000000000655+300+FAKEDIALOGIDabcdefghijklmnopqr+1+FAKEDIALOGIDabcdefghijklmnopqr:1'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghijkl+1:20190102:030405+2:2:13:@8@\x00\x00\x00\x00\x00\x00\x00\x00:5:1+280:20010020:PRIVATE____:V:0:0+0'HNVSD:999:1+@400@HIRMG:2:2+0020::Dialoginitialisierung erfolgreich.+3075::Starke Authentifizierung ab dem 2019-09-08 erforderlich.:2019-09-08+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+0020::Information fehlerfrei entgegengenommen.'HIRMS:4:2:4+3920::Meldung unterstützter Ein- und Zwei-Schritt-Verfahren:912:913:920:930'HIRMS:5:2:5+0020::Auftrag ausgeführt.'HISYN:6:4:5+FAKEKUNDENSYSTEMIDabcdefghijkl''HNHBS:7:1+1'";
    public const SYNC_END_REQUEST = "HNHBK:1:3+000000000426+300+FAKEDIALOGIDabcdefghijklmnopqr+2'HNVSK:998:3+PIN:1+998+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1:20190102:030405+2:2:13:@8@00000000:5:1+280:20010020:PRIVATE____:V:0:0+0'HNVSD:999:1+@204@HNSHK:2:4+PIN:1+999+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghijkl+1+1:20190102:030405+1:999:1+6:10:19+280:20010020:PRIVATE____:S:0:0'HKEND:3:1+FAKEDIALOGIDabcdefghijklmnopqr'HNSHA:4:2+9999999++PRIVATE___''HNHBS:5:1+2'";
    public const SYNC_END_RESPONSE = "HNHBK:1:3+000000000332+300+FAKEKUNDENSYSTEMIDabcdefghijkl+2+FAKEKUNDENSYSTEMIDabcdefghijkl:2'HNVSK:998:3+PIN:1+998+1+2::FAKEKUNDENSYSTEMIDabcdefghijkl+1:20190102:030405+2:2:13:@8@\x00\x00\x00\x00\x00\x00\x00\x00:5:1+280:20010020:PRIVATE____:V:0:0+0'HNVSD:999:1+@78@HIRMG:2:2+0010::Nachricht entgegengenommen.'HIRMS:3:2:3+0100::Dialog beendet.''HNHBS:4:1+2'";

    // Dialog initialization for main dialog (HKVVB). As this is the first time we use strong authentication, we get the UPD.
    // Also, using strong (=regular) authentication allows us to leave off the HNHBK/HNVSD wrapper here and let FinTsTestCase handle it.
    public const INIT_REQUEST = "HKIDN:3:2+280:20010020+PRIVATE____+FAKEKUNDENSYSTEMIDabcdefghijkl+1'HKVVB:4:3+14+0+0+123456789ABCDEF0123456789+1.0'HKTAN:5:6+4+HKIDN+++++++++mT?:PRIVATE__'";
    public const INIT_RESPONSE = "HIRMG:2:2+0020::Dialoginitialisierung erfolgreich.+3076::Keine starke Authentifizierung erforderlich.+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+0020::Information fehlerfrei entgegengenommen.'HIRMS:4:2:4+3920::Meldung unterstützter Ein- und Zwei-Schritt-Verfahren:912:913:920:930+1050::UPD nicht mehr aktuell. Aktuelle Version folgt.'HIUPA:5:4:4+PRIVATE____+83+0+PRIVATE_________'HIUPD:6:6:4+PRIVATE__::280:20010020+DE18200100200PRIVATE__+PRIVATE____+1+EUR+PRIVATE__________________++PB Business Giro++HKSPA:1+DKTSP:1+HKTSP:1+DKPAE:1+HKPAE:1+HKPRO:1+HKTAB:1+HKTAN:1+DKPSA:1+HKPSA:1+HKCCS:1+HKCSE:1+HKCSB:1+HKCSL:1+HKCDE:1+HKCDB:1+HKCDL:1+HKSAL:1+HKKAZ:1+HKEKA:1+HKKAU:1+HKCDN:1+HKCCM:1+HKCME:1+HKCMB:1+HKCML:1+HKDME:1+HKBME:1+HKDSC:1+HKDMC:1'HIUPD:7:6:4+++PRIVATE____+++PRIVATE_________++++HKSPA:1+DKTSP:1+HKTSP:1+DKPAE:1+HKPAE:1+HKPRO:1+HKTAB:1+HKTAN:1+DKPSA:1+HKPSA:1'HITAN:8:6:5+4++noref+nochallenge'";

    // Dialog end for main dialog (HKEND).
    public const FINAL_END_REQUEST = "HKEND:3:1+FAKEDIALOGIDabcdefghijklmnopqr'";
    public const FINAL_END_RESPONSE = "HIRMG:2:2+0010::Nachricht entgegengenommen.'HIRMS:3:2:3+0100::Dialog beendet.'";

    /**
     * Executes dialog synchronization and initialization, so that BPD and UPD are filled.
     * @throws \Throwable
     */
    protected function initDialog()
    {
        // We already know the TAN mode, so it will only fetch the BPD (anonymously) to verify it.
        $this->expectMessage(static::ANONYMOUS_INIT_REQUEST, static::ANONYMOUS_INIT_RESPONSE);
        $this->expectMessage(static::ANONYMOUS_END_REQUEST, static::ANONYMOUS_END_RESPONSE);

        // Then when we initialize a dialog, it's going to request a Kundensystem-ID and UPD.
        $this->expectMessage(static::SYNC_REQUEST, mb_convert_encoding(static::SYNC_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->expectMessage(static::SYNC_END_REQUEST, static::SYNC_END_RESPONSE);
        // And finally it can initialize the main dialog.
        $this->expectMessage(static::INIT_REQUEST, mb_convert_encoding(static::INIT_RESPONSE, 'ISO-8859-1', 'UTF-8'));

        $this->fints->selectTanMode(intval(self::TEST_TAN_MODE), 'mT:PRIVATE__');
        $login = $this->fints->login();
        $login->ensureDone(); // No TAN required upon login.
        $this->assertAllMessagesSeen();
    }

    /**
     * @return SEPAAccount
     */
    protected function getTestAccount()
    {
        $sepaAccount = new SEPAAccount();
        $sepaAccount->setIban('DExxABCDEFGH1234567890');
        $sepaAccount->setBic('PBNKDEFF');
        $sepaAccount->setAccountNumber('1234567890');
        $sepaAccount->setBlz(self::TEST_BANK_CODE);
        return $sepaAccount;
    }
}
