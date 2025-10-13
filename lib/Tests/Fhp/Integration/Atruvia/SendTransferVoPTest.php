<?php

namespace Tests\Fhp\Integration\Atruvia;

use Fhp\Action\SendSEPATransferVoP;

class SendTransferVoPTest extends AtruviaIntegrationTestBase
{
    public const XML_PAYLOAD = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<Document xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.001.001.09 pain.001.001.09.xsd" xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.09"><CstmrCdtTrfInitn><GrpHdr><MsgId>M1234567890</MsgId><CreDtTm>2025-10-10T12:52:56+02:00</CreDtTm><NbOfTxs>1</NbOfTxs><CtrlSum>10.00</CtrlSum><InitgPty><Nm>PRIVATE__________________</Nm></InitgPty></GrpHdr><PmtInf><PmtInfId>P12345678</PmtInfId><PmtMtd>TRF</PmtMtd><NbOfTxs>1</NbOfTxs><CtrlSum>10.00</CtrlSum><PmtTpInf><SvcLvl><Cd>SEPA</Cd></SvcLvl></PmtTpInf><ReqdExctnDt><Dt>1999-01-01</Dt></ReqdExctnDt><Dbtr><Nm>PRIVATE__________________</Nm></Dbtr><DbtrAcct><Id><IBAN>DE00ABCDEFGH1234567890</IBAN></Id></DbtrAcct><DbtrAgt><FinInstnId><BICFI>ABCDEFGHIJK</BICFI></FinInstnId></DbtrAgt><ChrgBr>SLEV</ChrgBr><CdtTrfTxInf><PmtId><EndToEndId>NOTPROVIDED</EndToEndId></PmtId><Amt><InstdAmt Ccy="EUR">10.00</InstdAmt></Amt><Cdtr><Nm>Empfänger</Nm></Cdtr><CdtrAcct><Id><IBAN>DE00ABCDEFGH1234567890</IBAN></Id></CdtrAcct><RmtInf><Ustrd>Testüberweisung</Ustrd></RmtInf></CdtTrfTxInf></PmtInf></CstmrCdtTrfInitn></Document>';

    public const SEND_TRANSFER_REQUEST =
        "HKVPP:3:1+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10'HKCCS:4:1+DE00ABCDEFGH1234567890:ABCDEFGHIJK:1234567890::280:11223344+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.09+@1161@" .
        self::XML_PAYLOAD .
        "'HKTAN:5:7+4+HKCCS'"
    ;
    public const SEND_TRANSFER_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.+3905::Es wurde keine Challenge erzeugt.'HIRMS:4:2:3+3040::Es liegen weitere Informationen vor.:staticscrollref'HIRMS:5:2:5+3945::Freigabe ohne VOP-Bestätigung nicht möglich.'HIVPP:6:1:3+++@36@c0f5c2a4-ebb7-4e72-be44-c68742177a2b+++++2'";

    public const POLL_VOP_REPORT_REQUEST = "HKVPP:3:1+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10+@36@c0f5c2a4-ebb7-4e72-be44-c68742177a2b++staticscrollref'";

    public const POLL_VOP_REPORT_MATCH_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0020::Auftrag ausgeführt.+0025::Keine Namensabweichung.'HIVPP:5:1:3+@36@5e3b5c99-df27-4d42-835b-18b35d0c66ff+++urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10+@3616@UTF8XMLPAYLOAD'";

    public const POLL_VOP_REPORT_MATCH_RESPONSE_XML_PAYLOAD = "<?xml version='1.0' encoding='UTF-8'?><Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.002.001.10\"><CstmrPmtStsRpt><GrpHdr><MsgId>ATRUVIA-20251013-125258-XXXXXXXXXXXXXXXX</MsgId><CreDtTm>2025-10-13T11:36:04.201+02:00</CreDtTm><DbtrAgt><FinInstnId><BICFI>ABCDEFGHIJK</BICFI></FinInstnId></DbtrAgt></GrpHdr><OrgnlGrpInfAndSts><OrgnlMsgId>M1234567890</OrgnlMsgId><OrgnlMsgNmId>pain.001.001.09</OrgnlMsgNmId><OrgnlNbOfTxs>1</OrgnlNbOfTxs><OrgnlCtrlSum>100.00</OrgnlCtrlSum><GrpSts>RCVC</GrpSts><StsRsnInf><AddtlInf>RCVC Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt mit dem für diese IBAN</AddtlInf><AddtlInf>RCVC hinterlegten Namen bei der Zahlungsempfängerbank überein.</AddtlInf><AddtlInf>RVMC Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt nahezu mit dem für diese IBAN</AddtlInf><AddtlInf>RVMC hinterlegten Namen bei der Zahlungsempfängerbank überein. Die Autorisierung der Zahlung</AddtlInf><AddtlInf>RVMC kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber nicht</AddtlInf><AddtlInf>RVMC der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht für</AddtlInf><AddtlInf>RVMC die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf><AddtlInf>RVNM Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt nicht mit dem für diese IBAN hinter-</AddtlInf><AddtlInf>RVNM legten Namen bei der Zahlungsempfängerbank überein. Bitte prüfen Sie den Empfängernamen. Die Autori-</AddtlInf><AddtlInf>RVNM sierung der Zahlung kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber</AddtlInf><AddtlInf>RVNM nicht der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht</AddtlInf><AddtlInf>RVNM für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf><AddtlInf>RVNA Der von Ihnen eingegebene Name des Zahlungsempfängers konnte nicht mit dem für diese IBAN hinter-</AddtlInf><AddtlInf>RVNA legten Namen bei der Zahlungsempfängerbank abgeglichen werden (z.B. technischer Fehler). Die Autori-</AddtlInf><AddtlInf>RVNA sierung der Zahlung kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber</AddtlInf><AddtlInf>RVNA nicht der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht</AddtlInf><AddtlInf>RVNA für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf></StsRsnInf><NbOfTxsPerSts><DtldNbOfTxs>1</DtldNbOfTxs><DtldSts>RCVC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVMC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNM</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNA</DtldSts></NbOfTxsPerSts></OrgnlGrpInfAndSts><OrgnlPmtInfAndSts><OrgnlPmtInfId>1760348162</OrgnlPmtInfId><OrgnlNbOfTxs>1</OrgnlNbOfTxs><NbOfTxsPerSts><DtldNbOfTxs>1</DtldNbOfTxs><DtldSts>RCVC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVMC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNM</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNA</DtldSts></NbOfTxsPerSts><TxInfAndSts><OrgnlEndToEndId>NOTPROVIDED</OrgnlEndToEndId><TxSts>RCVC</TxSts><OrgnlTxRef><Cdtr><Pty><Nm>Testempfänger</Nm></Pty></Cdtr><CdtrAcct><Id><IBAN>DE00ABCDEFGH1234567890</IBAN></Id></CdtrAcct></OrgnlTxRef></TxInfAndSts></OrgnlPmtInfAndSts></CstmrPmtStsRpt></Document>";

    public const POLL_VOP_REPORT_NO_MATCH_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+3090::Ergebnis des Namensabgleichs prüfen.'HIVPP:5:1:3+@36@5e3b5c99-df27-4d42-835b-18b35d0c66ff+++urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.002.001.10+@3600@UTF8XMLPAYLOAD++Bei mindestens einem Zahlungsempfänger stimmt der Name mit dem für diese IBAN bei der Zahlungsempfängerbank hinterlegten Namen <b>nicht</b> oder nur <b>nahezu</b> überein.<br>Alternativ konnte der Name des Zahlungsempfängers nicht mit dem bei der Zahlungsempfängerbank hinterlegten Namen abgeglichen werden.<p>Eine nicht mögliche Empfängerüberprüfung kann auftreten, wenn ein technisches Problem vorliegt, die Empfängerbank diesen Service nicht anbietet oder eine Prüfung für das Empfängerkonto nicht möglich ist.<p><b>Wichtiger Hinweis?:</b> Die Überweisung wird ohne Korrektur ausgeführt.<p>Dies kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber nicht der von Ihnen angegebene Empfänger ist.<br>In diesem Fall haftet die Bank nicht für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.<p>Eine Haftung der an der Ausführung der Überweisung beteiligten Zahlungsdienstleister ist ebenfalls ausgeschlossen.'";

    public const POLL_VOP_REPORT_NO_MATCH_RESPONSE_XML_PAYLOAD = "<?xml version='1.0' encoding='UTF-8'?><Document xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.002.001.10\"><CstmrPmtStsRpt><GrpHdr><MsgId>ATRUVIA-20251010-125258-X</MsgId><CreDtTm>2025-10-10T12:52:58.283+02:00</CreDtTm><DbtrAgt><FinInstnId><BICFI>ABCDEFGHIJK</BICFI></FinInstnId></DbtrAgt></GrpHdr><OrgnlGrpInfAndSts><OrgnlMsgId>M1234567890</OrgnlMsgId><OrgnlMsgNmId>pain.001.001.09</OrgnlMsgNmId><OrgnlNbOfTxs>1</OrgnlNbOfTxs><OrgnlCtrlSum>10.00</OrgnlCtrlSum><GrpSts>RVCM</GrpSts><StsRsnInf><AddtlInf>RCVC Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt mit dem für diese IBAN</AddtlInf><AddtlInf>RCVC hinterlegten Namen bei der Zahlungsempfängerbank überein.</AddtlInf><AddtlInf>RVMC Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt nahezu mit dem für diese IBAN</AddtlInf><AddtlInf>RVMC hinterlegten Namen bei der Zahlungsempfängerbank überein. Die Autorisierung der Zahlung</AddtlInf><AddtlInf>RVMC kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber nicht</AddtlInf><AddtlInf>RVMC der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht für</AddtlInf><AddtlInf>RVMC die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf><AddtlInf>RVNM Der von Ihnen eingegebene Name des Zahlungsempfängers stimmt nicht mit dem für diese IBAN hinter-</AddtlInf><AddtlInf>RVNM legten Namen bei der Zahlungsempfängerbank überein. Bitte prüfen Sie den Empfängernamen. Die Autori-</AddtlInf><AddtlInf>RVNM sierung der Zahlung kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber</AddtlInf><AddtlInf>RVNM nicht der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht</AddtlInf><AddtlInf>RVNM für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf><AddtlInf>RVNA Der von Ihnen eingegebene Name des Zahlungsempfängers konnte nicht mit dem für diese IBAN hinter-</AddtlInf><AddtlInf>RVNA legten Namen bei der Zahlungsempfängerbank abgeglichen werden (z.B. technischer Fehler). Die Autori-</AddtlInf><AddtlInf>RVNA sierung der Zahlung kann dazu führen, dass das Geld auf ein Konto überwiesen wird, dessen Inhaber</AddtlInf><AddtlInf>RVNA nicht der von Ihnen angegebene Empfänger ist. In diesem Fall haften die Zahlungsdienstleister nicht</AddtlInf><AddtlInf>RVNA für die Folgen der fehlenden Übereinstimmung, insbesondere besteht kein Anspruch auf Rückerstattung.</AddtlInf></StsRsnInf><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RCVC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVMC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>1</DtldNbOfTxs><DtldSts>RVNM</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNA</DtldSts></NbOfTxsPerSts></OrgnlGrpInfAndSts><OrgnlPmtInfAndSts><OrgnlPmtInfId>1760093576</OrgnlPmtInfId><OrgnlNbOfTxs>1</OrgnlNbOfTxs><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RCVC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVMC</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>1</DtldNbOfTxs><DtldSts>RVNM</DtldSts></NbOfTxsPerSts><NbOfTxsPerSts><DtldNbOfTxs>0</DtldNbOfTxs><DtldSts>RVNA</DtldSts></NbOfTxsPerSts><TxInfAndSts><OrgnlEndToEndId>NOTPROVIDED</OrgnlEndToEndId><TxSts>RVNM</TxSts><OrgnlTxRef><Cdtr><Pty><Nm>Testempfänger</Nm></Pty></Cdtr><CdtrAcct><Id><IBAN>DE00ABCDEFGH1234567890</IBAN></Id></CdtrAcct></OrgnlTxRef></TxInfAndSts></OrgnlPmtInfAndSts></CstmrPmtStsRpt></Document>";

    public const CONFIRM_VOP_REQUEST =
        "HKVPA:3:1+@36@5e3b5c99-df27-4d42-835b-18b35d0c66ff'HKCCS:4:1+DE00ABCDEFGH1234567890:ABCDEFGHIJK:1234567890::280:11223344+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.09+@1161@" .
        self::XML_PAYLOAD .
        "'HKTAN:5:7+4+HKCCS'"
    ;
    public const CONFIRM_VOP_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+0020::Ausführungsbestätigung nach Namensabgleich erhalten.'HIRMS:5:2:5+3955::Sicherheitsfreigabe erfolgt über anderen Kanal.'HITAN:6:7:5+4++1234567890123456789012345678+Bitte bestätigen Sie den Vorgang in Ihrer SecureGo plus App'";

    public const CHECK_DECOUPLED_SUBMISSION_REQUEST = "HKTAN:3:7+S++++1234567890123456789012345678+N'";
    public const CHECK_DECOUPLED_SUBMISSION_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0020::*SEPA-Einzelüberweisung erfolgreich+0900::Freigabe erfolgreich'HITAN:5:7:3+S++1234567890123456789012345678'";

    /**
     * @throws \Throwable
     */
    protected function testVop(string $requestVopReportResponse)
    {
        $this->initDialog();

        $transferAction = $this->getTransferAction();

        $this->expectMessage(static::SEND_TRANSFER_REQUEST, mb_convert_encoding(static::SEND_TRANSFER_RESPONSE, 'ISO-8859-1', 'UTF-8'));

        $this->fints->execute($transferAction);

        while ($transferAction->needsTime()) {
            # As this is a test, we don't need to actually wait.
            #$wait = $transferAction->hivpp->wartezeitVorNaechsterAbfrage;
            #sleep($wait);

            $this->expectMessage(static::POLL_VOP_REPORT_REQUEST, $requestVopReportResponse);

            $this->fints->execute($transferAction);
        }

        if ($transferAction->needsConfirmation()) {
            $transferAction->setConfirmed();
            $this->expectMessage(static::CONFIRM_VOP_REQUEST, mb_convert_encoding(static::CONFIRM_VOP_RESPONSE, 'ISO-8859-1', 'UTF-8'));
            $this->fints->execute($transferAction);
        }

        $tanMode = $this->fints->getSelectedTanMode();
        while ($transferAction->needsTan()) {

            if ($tanMode->isDecoupled()) {
                $this->expectMessage(static::CHECK_DECOUPLED_SUBMISSION_REQUEST, mb_convert_encoding(static::CHECK_DECOUPLED_SUBMISSION_RESPONSE, 'ISO-8859-1', 'UTF-8'));
                $this->fints->checkDecoupledSubmission($transferAction);
            }
        }

        $transferAction->ensureDone();
    }

    public function testVopNoMatch()
    {
        $requestVopReportResponse = str_replace('UTF8XMLPAYLOAD', self::POLL_VOP_REPORT_NO_MATCH_RESPONSE_XML_PAYLOAD, mb_convert_encoding(static::POLL_VOP_REPORT_NO_MATCH_RESPONSE, 'ISO-8859-1', 'UTF-8'));

        $this->testVop($requestVopReportResponse);
    }

    public function testVopMatch()
    {
        $requestVopReportResponse = str_replace('UTF8XMLPAYLOAD', self::POLL_VOP_REPORT_MATCH_RESPONSE_XML_PAYLOAD, mb_convert_encoding(static::POLL_VOP_REPORT_MATCH_RESPONSE, 'ISO-8859-1', 'UTF-8'));

        $this->testVop($requestVopReportResponse);
    }

    protected function getTransferAction(): SendSEPATransferVoP
    {
        $account = $this->getTestAccount();
        return SendSEPATransferVoP::create($account, self::XML_PAYLOAD);
    }
}
