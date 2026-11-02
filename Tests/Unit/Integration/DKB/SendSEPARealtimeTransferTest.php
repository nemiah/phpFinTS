<?php

namespace Fhp\Tests\Unit\Integration\DKB;

use Fhp\Action\GetSEPARealtimeTransferStatus;
use Fhp\Action\SendSEPARealtimeTransfer;
use Fhp\Segment\IPS\HIIPSSv1;
use Fhp\Syntax\Parser;

class SendSEPARealtimeTransferTest extends DKBIntegrationTestBase
{
    // The XML payload. Its content does not matter here, only the namespace has to be supported by the bank.
    public const PAIN_MESSAGE = '<?xml version="1.0" encoding="UTF-8"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03"><CstmrCdtTrfInitn/></Document>';

    public const SEND_TRANSFER_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:4+0030::Auftrag empfangen - Bitte die empfangene TAN eingeben.'HITAN:5:6:4+4++2472-12-07-21.27.57.456789+Bitte geben Sie die pushTAN ein.+++SomePhone1'";
    public const SEND_TAN_REQUEST = "HNHBK:1:3+000000000425+300+FAKEDIALOGIDabcdefghijklmnopqr+3'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@206@HNSHK:2:4+PIN:2+921+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HKTAN:3:6+2++++2472-12-07-21.27.57.456789+N'HNSHA:4:2+9999999++12345:666555''HNHBS:5:1+3'";
    public const SEND_TAN_RESPONSE_EXECUTED = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0020::Auftrag ausgeführt.'HITAN:5:6:3+2++2472-12-07-21.27.57.456789'HIIPZ:6:1:3+C6592DB8C24247C493F1B27377EC63DA++7'";
    // Real-world response (comdirect, 2026-10-01) when the payee's bank did not answer in time.
    public const SEND_TAN_RESPONSE_PENDING = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+3045::Wir haben leider noch keine Rückmeldung zum Status ihrer Überweisung.'HITAN:5:6:3+2++2472-12-07-21.27.57.456789'HIIPZ:6:1:3+C6592DB8C24247C493F1B27377EC63DA'";

    public const STATUS_REQUEST = "HKIPS:3:1+DExxABCDEFGH1234567890:BYLADEM1001+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03+C6592DB8C24247C493F1B27377EC63DA'";
    public const STATUS_RESPONSE_PENDING = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+3045::SEPA-Instant Payment Statusabfrage HKIPS erneut durchführen'";
    public const STATUS_RESPONSE_EXECUTED = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0020::Geld für den Empfänger verfügbar'HIIPS:5:1:3+DExxABCDEFGH1234567890:BYLADEM1001+++C6592DB8C24247C493F1B27377EC63DA++7'";

    private static function getSendTransferRequest(): string
    {
        return 'HKIPZ:3:1+DExxABCDEFGH1234567890:BYLADEM1001+urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03+@'
            . strlen(self::PAIN_MESSAGE) . '@' . self::PAIN_MESSAGE . "'HKTAN:4:6+4+HKIPZ+++++++++SomePhone1'";
    }

    private function sendTransfer(string $tanResponse): SendSEPARealtimeTransfer
    {
        $this->expectMessage(self::getSendTransferRequest(), static::SEND_TRANSFER_RESPONSE);
        $transfer = SendSEPARealtimeTransfer::create($this->getTestAccount(), static::PAIN_MESSAGE, false);
        $this->fints->execute($transfer);
        $this->assertTrue($transfer->needsTan());

        $this->expectMessage(static::SEND_TAN_REQUEST, $tanResponse);
        $this->fints->submitTan($transfer, '666555');
        $this->assertTrue($transfer->isDone());
        return $transfer;
    }

    /**
     * @throws \Throwable
     */
    public function testExecuted(): void
    {
        $this->initDialog();
        $transfer = $this->sendTransfer(static::SEND_TAN_RESPONSE_EXECUTED);

        $this->assertFalse($transfer->isStatusPending());
        $this->assertSame('C6592DB8C24247C493F1B27377EC63DA', $transfer->getOrderId());
        $this->assertSame(7, $transfer->getStatus());
    }

    /**
     * @throws \Throwable
     */
    public function testStatusPendingThenExecuted(): void
    {
        $this->initDialog();
        $transfer = $this->sendTransfer(static::SEND_TAN_RESPONSE_PENDING);

        $this->assertTrue($transfer->isStatusPending());
        $this->assertSame('C6592DB8C24247C493F1B27377EC63DA', $transfer->getOrderId());
        $this->assertNull($transfer->getStatus());
        $this->assertSame(10, GetSEPARealtimeTransferStatus::getMinimumWaitingTime($this->fints->getBpd()));

        // First status request: still no answer from the payee's bank.
        $this->expectMessage(static::STATUS_REQUEST, static::STATUS_RESPONSE_PENDING);
        $status = GetSEPARealtimeTransferStatus::create($this->getTestAccount(), $transfer->getOrderId(), 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');
        $this->fints->execute($status);
        $this->assertFalse($status->needsTan());
        $this->assertTrue($status->isStatusPending());
        $this->assertNull($status->getStatus());
        $this->assertFalse($status->isExecuted());

        // Second status request: executed. The supported formats now come from the BPD.
        $this->expectMessage(static::STATUS_REQUEST, static::STATUS_RESPONSE_EXECUTED);
        $status = GetSEPARealtimeTransferStatus::create($this->getTestAccount(), $transfer->getOrderId());
        $this->fints->execute($status);
        $this->assertFalse($status->isStatusPending());
        $this->assertSame(7, $status->getStatus());
        $this->assertTrue($status->isExecuted());
        $this->assertFalse($status->isFailed());
    }

    public function testParseBankParameters(): void
    {
        // Real-world BPD segments (comdirect and DKB, 2026-10).
        /** @var HIIPSSv1 $comdirect */
        $comdirect = Parser::detectAndParseSegment("HIIPSS:21:1:3+999+1+0+99:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.09:urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.09.xsd'");
        $this->assertInstanceOf(HIIPSSv1::class, $comdirect);
        $this->assertSame(99, $comdirect->parameter->mindestwartezeit);
        $this->assertSame(['urn:iso:std:iso:20022:tech:xsd:pain.001.001.09', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.09'], $comdirect->parameter->getUnterstuetzteSEPADatenformate());

        /** @var HIIPSSv1 $dkb */
        $dkb = Parser::detectAndParseSegment("HIIPSS:43:1:3+1+1+0+5'");
        $this->assertSame(5, $dkb->parameter->mindestwartezeit);
        $this->assertSame([], $dkb->parameter->getUnterstuetzteSEPADatenformate());
    }
}
