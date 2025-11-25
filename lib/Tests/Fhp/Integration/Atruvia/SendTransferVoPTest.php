<?php

namespace Tests\Fhp\Integration\Atruvia;

use Fhp\Action\SendSEPATransfer;
use Fhp\Model\VopVerificationResult;
use Fhp\Segment\VPP\HIVPPv1;
use Fhp\Syntax\Bin;
use Fhp\Syntax\Parser;
use Fhp\Syntax\Serializer;

class SendTransferVoPTest extends AtruviaIntegrationTestBase
{
    public const XML_PAYLOAD = (
        '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.001.001.09 pain.001.001.09.xsd" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.09"><CstmrCdtTrfInitn><GrpHdr><MsgId>M1234567890</MsgId><CreDtTm>2025-10-10T12:52:56+02:00</CreDtTm><NbOfTxs>1</NbOfTxs><CtrlSum>10.00</CtrlSum><InitgPty><Nm>PRIVATE__________________</Nm></InitgPty></GrpHdr><PmtInf><PmtInfId>P12345678</PmtInfId><PmtMtd>TRF</PmtMtd><NbOfTxs>1</NbOfTxs><CtrlSum>10.00</CtrlSum><PmtTpInf><SvcLvl><Cd>SEPA</Cd></SvcLvl></PmtTpInf><ReqdExctnDt><Dt>1999-01-01</Dt></ReqdExctnDt><Dbtr><Nm>PRIVATE__________________</Nm></Dbtr><DbtrAcct><Id><IBAN>DE00ABCDEFGH1234567890</IBAN></Id></DbtrAcct><DbtrAgt><FinInstnId><BICFI>ABCDEFGHIJK</BICFI></FinInstnId></DbtrAgt><ChrgBr>SLEV</ChrgBr><CdtTrfTxInf><PmtId><EndToEndId>NOTPROVIDED</EndToEndId></PmtId><Amt><InstdAmt Ccy="EUR">10.00</InstdAmt></Amt><Cdtr><Nm>Empfänger</Nm></Cdtr><CdtrAcct><Id><IBAN>DE00ABCDEFGH1234567890</IBAN></Id></CdtrAcct><RmtInf><Ustrd>Testüberweisung</Ustrd></RmtInf></CdtTrfTxInf></PmtInf></CstmrCdtTrfInitn></Document>'
    );

    public const SEND_TRANSFER_REQUEST = (
        'HKCCS:3:1+DE00ABCDEFGH1234567890:ABCDEFGHIJK:1234567890::280:11223344+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.09+@1161@' .
        self::XML_PAYLOAD .
        "'HKTAN:4:7+4+HKCCS'HKVPP:5:1+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10'"
    );
    public const SEND_TRANSFER_RESPONSE_POLLING_NEEDED = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.+3905::Es wurde keine Challenge erzeugt.'HIRMS:4:2:5+3040::Es liegen weitere Informationen vor.:staticscrollref'HIRMS:5:2:4+3945::Freigabe ohne VOP-Bestätigung nicht möglich.'HIVPP:6:1:5+++@36@c0f5c2a4-ebb7-4e72-be44-c68742177a2b+++++2'";
    public const SEND_TRANSFER_RESPONSE_IMMEDIATE_SUCCESS = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.+3905::Es wurde keine Challenge erzeugt.'HIRMS:4:2:5+3091::VOP-Ausführungsauftrag nicht benötigt.+0025::Keine Namensabweichung.'HIRMS:5:2:4+3076::Keine starke Authentifizierung erforderlich.'HIVPP:6:1:5+@36@5e3b5c99-df27-4d42-835b-18b35d0c66ff+++urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10'HITAN:6:7:4+4++noref+nochallenge'HIRMS:7:2:3+0020::*SEPA-Einzelüberweisung erfolgreich+0900::Freigabe erfolgreich'";

    public const POLL_VOP_REQUEST = "HKVPP:3:1+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10+@36@c0f5c2a4-ebb7-4e72-be44-c68742177a2b++staticscrollref'";

    public const VOP_REPORT_MATCH_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0020::Auftrag ausgeführt.+0025::Keine Namensabweichung.'HIVPP:5:1:3+@36@5e3b5c99-df27-4d42-835b-18b35d0c66ff+++urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10'";
    public const VOP_REPORT_MATCH_NO_CONFIRMATION_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+3091::VOP-Ausführungsauftrag nicht benötigt.+0025::Keine Namensabweichung.'HIVPP:5:1:3+@36@5e3b5c99-df27-4d42-835b-18b35d0c66ff+++urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10'HITAN:6:7:5+4++1234567890123456789012345678+Bitte bestätigen Sie den Vorgang in Ihrer SecureGo plus App'";
    public const VOP_REPORT_MATCH_XML_PAYLOAD = "<?xml version='1.0' encoding='UTF-8'?><Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.002.001.10\"><CstmrPmtStsRpt><GrpHdr><MsgId>ATRUVIA-20251013-125258-XXXXXXXXXXXXXXXX</MsgId><CreDtTm>2025-10-13T11:36:04.201+02:00</CreDtTm><DbtrAgt><FinInstnId><BICFI>ABCDEFGHIJK</BICFI></FinInstnId></DbtrAgt></GrpHdr><OrgnlGrpInfAndSts><OrgnlMsgId>M1234567890</OrgnlMsgId><OrgnlMsgNmId>pain.001.001.09</OrgnlMsgNmId><OrgnlNbOfTxs>1</OrgnlNbOfTxs><OrgnlCtrlSum>100.00</OrgnlCtrlSum><GrpSts>RCVC</GrpSts><StsRsnInf><AddtlInf>RCVC Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt mit dem für diese IBAN</AddtlInf><AddtlInf>RCVC hinterlegten Namen bei der Zahlungsempfängerbank überein.</AddtlInf><AddtlInf>RVMC Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt nahezu mit dem für diese IBAN</AddtlInf><AddtlInf>RVMC hinterlegten Namen bei der Zahlungsempfängerbank überein. Die Autorisierung der Zahlung</AddtlInf><AddtlInf>RVMC kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber nicht</AddtlInf><AddtlInf>RVMC der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht für</AddtlInf><AddtlInf>RVMC die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf><AddtlInf>RVNM Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt nicht mit dem für diese IBAN hinter-</AddtlInf><AddtlInf>RVNM legten Namen bei der Zahlungsempfängerbank überein. Bitte prüfen Sie den Empfängernamen. Die Autori-</AddtlInf><AddtlInf>RVNM sierung der Zahlung kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber</AddtlInf><AddtlInf>RVNM nicht der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht</AddtlInf><AddtlInf>RVNM für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf><AddtlInf>RVNA Der von Ihnen eingegebene Name des Zahlungsempfängers konnte nicht mit dem für diese IBAN hinter-</AddtlInf><AddtlInf>RVNA legten Namen bei der Zahlungsempfängerbank abgeglichen werden (z.B. technischer Fehler). Die Autori-</AddtlInf><AddtlInf>RVNA sierung der Zahlung kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber</AddtlInf><AddtlInf>RVNA nicht der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht</AddtlInf><AddtlInf>RVNA für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf></StsRsnInf><NbOfTxsPerSts><DtldNbOfTxs>1</DtldNbOfTxs><DtldSts>RCVC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVMC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNM</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNA</DtldSts></NbOfTxsPerSts></OrgnlGrpInfAndSts><OrgnlPmtInfAndSts><OrgnlPmtInfId>1760348162</OrgnlPmtInfId><OrgnlNbOfTxs>1</OrgnlNbOfTxs><NbOfTxsPerSts><DtldNbOfTxs>1</DtldNbOfTxs><DtldSts>RCVC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVMC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNM</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNA</DtldSts></NbOfTxsPerSts><TxInfAndSts><OrgnlEndToEndId>NOTPROVIDED</OrgnlEndToEndId><TxSts>RCVC</TxSts><OrgnlTxRef><Cdtr><Pty><Nm>Testempfänger</Nm></Pty></Cdtr><CdtrAcct><Id><IBAN>DE00ABCDEFGH1234567890</IBAN></Id></CdtrAcct></OrgnlTxRef></TxInfAndSts></OrgnlPmtInfAndSts></CstmrPmtStsRpt></Document>";

    public const VOP_REPORT_PARTIAL_MATCH_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+3090::Ergebnis des Namensabgleichs prüfen.'HIVPP:5:1:3+@36@5e3b5c99-df27-4d42-835b-18b35d0c66ff+++urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10+++Bei mindestens einem Zahlungsempfänger stimmt der Name mit dem für diese IBAN bei der Zahlungsempfängerbank hinterlegten Namen <b>nicht</b> oder nur <b>nahezu</b> überein.<br>Alternativ konnte der Name des Zahlungsempfängers nicht mit dem bei der Zahlungsempfängerbank hinterlegten Namen abgeglichen werden.<p>Eine nicht mögliche Empfängerüberprüfung kann auftreten, wenn ein technisches Problem vorliegt, die Empfängerbank diesen Service nicht anbietet oder eine Prüfung für das Empfängerkonto nicht möglich ist.<p><b>Wichtiger Hinweis?:</b> Die Überweisung wird ohne Korrektur ausgeführt.<p>Dies kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber nicht der von Ihnen angegebene Empfänger ist.<br>In diesem Fall haftet die Bank nicht für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.<p>Eine Haftung der an der Ausführung der Überweisung beteiligten Zahlungsdienstleister ist ebenfalls ausgeschlossen.'";
    public const VOP_REPORT_PARTIAL_MATCH_XML_PAYLOAD = "<?xml version='1.0' encoding='UTF-8'?><Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.002.001.10\"><CstmrPmtStsRpt><GrpHdr><MsgId>ATRUVIA-20251010-125258-X</MsgId><CreDtTm>2025-10-10T12:52:58.283+02:00</CreDtTm><DbtrAgt><FinInstnId><BICFI>ABCDEFGHIJK</BICFI></FinInstnId></DbtrAgt></GrpHdr><OrgnlGrpInfAndSts><OrgnlMsgId>M1234567890</OrgnlMsgId><OrgnlMsgNmId>pain.001.001.09</OrgnlMsgNmId><OrgnlNbOfTxs>1</OrgnlNbOfTxs><OrgnlCtrlSum>10.00</OrgnlCtrlSum><GrpSts>RVCM</GrpSts><StsRsnInf><AddtlInf>RCVC Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt mit dem für diese IBAN</AddtlInf><AddtlInf>RCVC hinterlegten Namen bei der Zahlungsempfängerbank überein.</AddtlInf><AddtlInf>RVMC Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt nahezu mit dem für diese IBAN</AddtlInf><AddtlInf>RVMC hinterlegten Namen bei der Zahlungsempfängerbank überein. Die Autorisierung der Zahlung</AddtlInf><AddtlInf>RVMC kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber nicht</AddtlInf><AddtlInf>RVMC der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht für</AddtlInf><AddtlInf>RVMC die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf><AddtlInf>RVNM Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt nicht mit dem für diese IBAN hinter-</AddtlInf><AddtlInf>RVNM legten Namen bei der Zahlungsempfängerbank überein. Bitte prüfen Sie den Empfängernamen. Die Autori-</AddtlInf><AddtlInf>RVNM sierung der Zahlung kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber</AddtlInf><AddtlInf>RVNM nicht der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht</AddtlInf><AddtlInf>RVNM für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf><AddtlInf>RVNA Der von Ihnen eingegebene Name des Zahlungsempfängers konnte nicht mit dem für diese IBAN hinter-</AddtlInf><AddtlInf>RVNA legten Namen bei der Zahlungsempfängerbank abgeglichen werden (z.B. technischer Fehler). Die Autori-</AddtlInf><AddtlInf>RVNA sierung der Zahlung kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber</AddtlInf><AddtlInf>RVNA nicht der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht</AddtlInf><AddtlInf>RVNA für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf></StsRsnInf><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RCVC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVMC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>1</DtldNbOfTxs><DtldSts>RVNM</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNA</DtldSts></NbOfTxsPerSts></OrgnlGrpInfAndSts><OrgnlPmtInfAndSts><OrgnlPmtInfId>1760093576</OrgnlPmtInfId><OrgnlNbOfTxs>1</OrgnlNbOfTxs><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RCVC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVMC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>1</DtldNbOfTxs><DtldSts>RVNM</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNA</DtldSts></NbOfTxsPerSts><TxInfAndSts><OrgnlEndToEndId>NOTPROVIDED</OrgnlEndToEndId><TxSts>RVNM</TxSts><OrgnlTxRef><Cdtr><Pty><Nm>Testempfänger</Nm></Pty></Cdtr><CdtrAcct><Id><IBAN>DE00ABCDEFGH1234567890</IBAN></Id></CdtrAcct></OrgnlTxRef></TxInfAndSts></OrgnlPmtInfAndSts></CstmrPmtStsRpt></Document>";

    public const CONFIRM_VOP_REQUEST = (
        'HKCCS:3:1+DE00ABCDEFGH1234567890:ABCDEFGHIJK:1234567890::280:11223344+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.09+@1161@'
        . self::XML_PAYLOAD
        . "'HKVPA:4:1+@36@5e3b5c99-df27-4d42-835b-18b35d0c66ff'HKTAN:5:7+4+HKCCS'"
    );
    public const CONFIRM_VOP_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:4+0020::Ausführungsbestätigung nach Namensabgleich erhalten.'HIRMS:5:2:5+3955::Sicherheitsfreigabe erfolgt über anderen Kanal.'HITAN:6:7:5+4++1234567890123456789012345678+Bitte bestätigen Sie den Vorgang in Ihrer SecureGo plus App'";

    public const CHECK_DECOUPLED_SUBMISSION_REQUEST = "HKTAN:3:7+S++++1234567890123456789012345678+N'";
    public const CHECK_DECOUPLED_SUBMISSION_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0020::*SEPA-Einzelüberweisung erfolgreich+0900::Freigabe erfolgreich'HITAN:5:7:3+S++1234567890123456789012345678'";

    /**
     * @throws \Throwable
     */
    public function testVopWithResultMatchButConfirmationRequired(): void
    {
        $this->initDialog();
        $action = $this->createAction();

        // We send the transfer and the bank asks to wait while VOP is happening.
        $this->expectMessage(static::SEND_TRANSFER_REQUEST, mb_convert_encoding(static::SEND_TRANSFER_RESPONSE_POLLING_NEEDED, 'ISO-8859-1', 'UTF-8'));
        $this->fints->execute($action);
        $this->assertTrue($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertFalse($action->needsTan());
        $this->assertFalse($action->isDone());
        $this->assertEquals(2, $action->getPollingInfo()->getNextAttemptInSeconds());

        // We poll the bank for the first and only time, now the VOP process is done, the result is available, and it's
        // a match (CompletedFullMatch). But the bank still asks for the VOP confirmation.
        $response = static::buildVopReportResponse(static::VOP_REPORT_MATCH_RESPONSE, static::VOP_REPORT_MATCH_XML_PAYLOAD);
        $this->expectMessage(static::POLL_VOP_REQUEST, $response);
        $this->fints->pollAction($action);
        $this->assertFalse($action->needsPollingWait());
        $this->assertTrue($action->needsVopConfirmation());
        $this->assertFalse($action->needsTan());
        $this->assertFalse($action->isDone());
        $this->assertEquals(
            VopVerificationResult::CompletedFullMatch,
            $action->getVopConfirmationRequest()->getVerificationResult()
        );

        // We confirm to the bank that it's okay to proceed, the bank asks for decoupled 2FA authentication.
        $this->expectMessage(static::CONFIRM_VOP_REQUEST, mb_convert_encoding(static::CONFIRM_VOP_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->fints->confirmVop($action);
        $this->assertFalse($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertTrue($action->needsTan());
        $this->assertFalse($action->isDone());
        $this->assertEquals(
            'Bitte bestätigen Sie den Vorgang in Ihrer SecureGo plus App',
            $action->getTanRequest()->getChallenge()
        );

        // After having completed the 2FA on the other device (not shown in this unit test), we ask the bank again, and
        // it confirms that the transfer was executed.
        $this->expectMessage(static::CHECK_DECOUPLED_SUBMISSION_REQUEST, mb_convert_encoding(static::CHECK_DECOUPLED_SUBMISSION_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->fints->checkDecoupledSubmission($action);
        $this->assertFalse($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertFalse($action->needsTan());
        $this->assertTrue($action->isDone());

        $action->ensureDone();
    }

    /**
     * @throws \Throwable
     */
    public function testVopWithResultPartialMatch(): void
    {
        $this->initDialog();
        $action = $this->createAction();

        // We send the transfer and the bank asks to wait while VOP is happening.
        $this->expectMessage(static::SEND_TRANSFER_REQUEST, mb_convert_encoding(static::SEND_TRANSFER_RESPONSE_POLLING_NEEDED, 'ISO-8859-1', 'UTF-8'));
        $this->fints->execute($action);
        $this->assertTrue($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertFalse($action->needsTan());
        $this->assertFalse($action->isDone());
        $this->assertEquals(2, $action->getPollingInfo()->getNextAttemptInSeconds());

        // We poll the bank for the first and only time, now the VOP process is done and the VOP result is available,
        // but the payee didn't match, and so we're being asked to confirm.
        $response = static::buildVopReportResponse(static::VOP_REPORT_PARTIAL_MATCH_RESPONSE, static::VOP_REPORT_PARTIAL_MATCH_XML_PAYLOAD);
        $this->expectMessage(static::POLL_VOP_REQUEST, $response);
        $this->fints->pollAction($action);
        $this->assertFalse($action->needsPollingWait());
        $this->assertTrue($action->needsVopConfirmation());
        $this->assertFalse($action->needsTan());
        $this->assertFalse($action->isDone());
        $this->assertEquals(
            VopVerificationResult::CompletedNoMatch,
            $action->getVopConfirmationRequest()->getVerificationResult()
        );

        // We confirm to the bank that it's okay to proceed, the bank asks for decoupled 2FA authentication.
        $this->expectMessage(static::CONFIRM_VOP_REQUEST, mb_convert_encoding(static::CONFIRM_VOP_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->fints->confirmVop($action);
        $this->assertFalse($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertTrue($action->needsTan());
        $this->assertFalse($action->isDone());
        $this->assertEquals(
            'Bitte bestätigen Sie den Vorgang in Ihrer SecureGo plus App',
            $action->getTanRequest()->getChallenge()
        );

        // After having completed the 2FA on the other device (not shown in this unit test), we ask the bank again, and
        // it confirms that the transfer was executed.
        $this->expectMessage(static::CHECK_DECOUPLED_SUBMISSION_REQUEST, mb_convert_encoding(static::CHECK_DECOUPLED_SUBMISSION_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->fints->checkDecoupledSubmission($action);
        $this->assertFalse($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertFalse($action->needsTan());
        $this->assertTrue($action->isDone());

        $action->ensureDone();
    }

    /**
     * This is a hypothetical test case in the sense that it wasn't recorded based on real traffic with the bank, but
     * constructed based on what the specification has to say.
     * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf (E.8.1.1.1 and exclude red part).
     * @throws \Throwable
     */
    public function testVopWithResultMatchWithoutConfirmation(): void
    {
        $this->initDialog();
        $action = $this->createAction();

        // We send the transfer and the bank asks to wait while VOP is happening.
        $this->expectMessage(static::SEND_TRANSFER_REQUEST, mb_convert_encoding(static::SEND_TRANSFER_RESPONSE_POLLING_NEEDED, 'ISO-8859-1', 'UTF-8'));
        $this->fints->execute($action);
        $this->assertTrue($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertFalse($action->needsTan());
        $this->assertFalse($action->isDone());
        $this->assertEquals(2, $action->getPollingInfo()->getNextAttemptInSeconds());

        // We poll the bank for the first and only time, now the VOP process is done, the result is available, and it's
        // a match (CompletedFullMatch). The bank does not want a VOP confirmation (as indicated by code 3091), so we
        // move straight on to 2FA authentication.
        $response = static::buildVopReportResponse(
            static::VOP_REPORT_MATCH_NO_CONFIRMATION_RESPONSE,
            static::VOP_REPORT_MATCH_XML_PAYLOAD
        );
        $this->expectMessage(static::POLL_VOP_REQUEST, $response);
        $this->fints->pollAction($action);
        $this->assertFalse($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertTrue($action->needsTan());
        $this->assertFalse($action->isDone());
        // Note: We currently lack an API for applications to retrieve the CompletedFullMatch result in this case,
        // because the VOP check itself is no longer actionable.
        $this->assertEquals(
            'Bitte bestätigen Sie den Vorgang in Ihrer SecureGo plus App',
            $action->getTanRequest()->getChallenge()
        );

        // After having completed the 2FA on the other device (not shown in this unit test), we ask the bank again, and
        // it confirms that the transfer was executed.
        $this->expectMessage(static::CHECK_DECOUPLED_SUBMISSION_REQUEST, mb_convert_encoding(static::CHECK_DECOUPLED_SUBMISSION_RESPONSE, 'ISO-8859-1', 'UTF-8'));
        $this->fints->checkDecoupledSubmission($action);
        $this->assertFalse($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertFalse($action->needsTan());
        $this->assertTrue($action->isDone());

        $action->ensureDone();
    }

    /**
     * This is a hypothetical test case in the sense that it wasn't recorded based on real traffic with the bank, but
     * constructed based on what the specification has to say.
     * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf (E.8.1 bullet point 2.).
     * @throws \Throwable
     */
    public function testVopWithResultImmediateSuccess(): void
    {
        $this->initDialog();
        $action = $this->createAction();

        // We send the transfer and the bank asks to wait while VOP is happening.
        $this->expectMessage(static::SEND_TRANSFER_REQUEST, mb_convert_encoding(static::SEND_TRANSFER_RESPONSE_IMMEDIATE_SUCCESS, 'ISO-8859-1', 'UTF-8'));
        $this->fints->execute($action);
        $this->assertFalse($action->needsPollingWait());
        $this->assertFalse($action->needsVopConfirmation());
        $this->assertFalse($action->needsTan());
        $this->assertTrue($action->isDone());

        $action->ensureDone();
    }

    protected function createAction(): SendSEPATransfer
    {
        return SendSEPATransfer::create($this->getTestAccount(), self::XML_PAYLOAD);
    }

    protected static function buildVopReportResponse(
        string $outerFintsMessageInUtf8,
        string $innerXmlInUtf8,
    ): string {
        $segments = Parser::parseSegments(mb_convert_encoding($outerFintsMessageInUtf8, 'ISO-8859-1', 'UTF-8'));
        foreach ($segments as $segment) {
            if ($segment instanceof HIVPPv1) {
                $segment->paymentStatusReport = new Bin($innerXmlInUtf8);
            }
        }
        return Serializer::serializeSegments($segments);
    }
}
