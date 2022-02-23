<?php

namespace Tests\Fhp\Integration\DKB;

use Fhp\Action\SendSEPATransfer;
use Tests\Fhp\FinTsPeer;

class SendSEPATransferTest extends DKBIntegrationTestBase
{
    // The XML payload.
    public const PAIN_MESSAGE = '<?xml version="1.0" encoding="UTF-8"?>
        <!--suppress HtmlUnknownTarget -->
        <Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.003.03"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.001.003.03 pain.001.003.03.xsd">
          <CstmrCdtTrfInitn>
            <GrpHdr>
              <MsgId>1575749897</MsgId>
              <CreDtTm>2019-12-07T20:18:17</CreDtTm>
              <NbOfTxs>1</NbOfTxs>
              <CtrlSum>42.42</CtrlSum>
              <InitgPty>
                <Nm>Dagobert Duck</Nm>
              </InitgPty>
            </GrpHdr>
            <PmtInf>
              <PmtInfId>1575749897</PmtInfId>
              <PmtMtd>TRF</PmtMtd>
              <NbOfTxs>1</NbOfTxs>
              <CtrlSum>42.42</CtrlSum>
              <PmtTpInf>
                <SvcLvl>
                  <Cd>SEPA</Cd>
                </SvcLvl>
              </PmtTpInf>
              <ReqdExctnDt>1999-01-01</ReqdExctnDt>
              <Dbtr>
                <Nm>Philipp Keck</Nm>
              </Dbtr>
              <DbtrAcct>
                <Id>
                  <IBAN>DE42000000001234567890</IBAN>
                </Id>
              </DbtrAcct>
              <DbtrAgt>
                <FinInstnId>
                  <BIC>BYLADEM1001</BIC>
                </FinInstnId>
              </DbtrAgt>
              <ChrgBr>SLEV</ChrgBr>
              <CdtTrfTxInf>
                <PmtId>
                  <EndToEndId>NOTPROVIDED</EndToEndId>
                </PmtId>
                <Amt>
                  <InstdAmt Ccy="EUR">42.42</InstdAmt>
                </Amt>
                <CdtrAgt>
                  <FinInstnId>
                    <BIC>CSDBDE71XXX</BIC>
                  </FinInstnId>
                </CdtrAgt>
                <Cdtr>
                  <Nm>Donald Duck</Nm>
                </Cdtr>
                <CdtrAcct>
                  <Id>
                    <IBAN>DE43987654321000000000</IBAN>
                  </Id>
                </CdtrAcct>
                <RmtInf>
                  <Ustrd>FinTS-Test-Transfer</Ustrd>
                </RmtInf>
              </CdtTrfTxInf>
            </PmtInf>
          </CstmrCdtTrfInitn>
        </Document>';

    // Transfer request (HKCCS) is below, the rest of the dialog is here.
    public const SEND_TRANSFER_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:4+0030::Auftrag empfangen - Bitte die empfangene TAN eingeben.(MBT62820200002)'HITAN:5:6:4+4++2472-12-07-21.27.57.456789+Bitte geben Sie die pushTAN ein.+++SomePhone1'";
    public const SEND_TAN_REQUEST = "HNHBK:1:3+000000000425+300+FAKEDIALOGIDabcdefghijklmnopqr+3'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@206@HNSHK:2:4+PIN:2+921+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HKTAN:3:6+2++++2472-12-07-21.27.57.456789+N'HNSHA:4:2+9999999++12345:666555''HNHBS:5:1+3'";
    public const SEND_TAN_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0010::Der Auftrag wurde entgegengenommen.'HITAN:5:6:3+2++2472-12-07-21.27.57.456789'";

    private function getSendTransferRequest(): string
    {
        // Note: strlen() is computed instead of hard-coded because it depends on the indentation in this file, which
        // may be changed by linters and other tools, and because it contains line breaks, which are different depending
        // the platform where this test runs.
        return 'HKCCS:3:1+DExxABCDEFGH1234567890:BYLADEM1001:1234567890::280:12030000+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.003.03+@'
            . strlen(self::PAIN_MESSAGE) . '@' . self::PAIN_MESSAGE . "'HKTAN:4:6+4+HKCCS+++++++++SomePhone1'";
    }

    /**
     * @throws \Throwable
     */
    private function runInitialRequest(): SendSEPATransfer
    {
        $sendTransfer = SendSEPATransfer::create($this->getTestAccount(), static::PAIN_MESSAGE);
        $this->fints->execute($sendTransfer);
        return $sendTransfer;
    }

    /**
     * @throws \Throwable
     */
    private function completeWithTan(SendSEPATransfer $sendTransfer)
    {
        $this->expectMessage(static::SEND_TAN_REQUEST, static::SEND_TAN_RESPONSE);
        $this->fints->submitTan($sendTransfer, '666555');
        $this->assertFalse($sendTransfer->needsTan());
        $this->assertTrue($sendTransfer->isDone());
    }

    /**
     * @throws \Throwable
     */
    public function testSendSEPATransfer()
    {
        $this->initDialog();

        $this->expectMessage($this->getSendTransferRequest(), static::SEND_TRANSFER_RESPONSE);
        $getStatement = $this->runInitialRequest();
        $this->assertTrue($getStatement->needsTan());
        $this->completeWithTan($getStatement);
    }

    /**
     * @throws \Throwable
     */
    public function testSendSEPATransferPersist()
    {
        $this->initDialog();

        $this->expectMessage($this->getSendTransferRequest(), static::SEND_TRANSFER_RESPONSE);
        $sendTransfer = $this->runInitialRequest();
        $this->assertTrue($sendTransfer->needsTan());

        // Pretend that we close everything and open everything from scratch, as if it were a new PHP process.
        $persistedInstance = $this->fints->persist();
        $persistedAction = serialize($sendTransfer);
        $this->connection->expects($this->once())->method('disconnect');
        $this->fints = new FinTsPeer($this->options, $this->credentials);
        $this->fints->loadPersistedInstance($persistedInstance);
        /** @var SendSEPATransfer $sendTransfer */
        $sendTransfer = unserialize($persistedAction);

        $this->completeWithTan($sendTransfer);
    }
}
