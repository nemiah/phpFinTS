<?php

namespace Tests\Fhp\Integration\DKB;

use Fhp\Action\GetStatementOfAccount;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\StatementOfAccount;
use Tests\Fhp\FinTsPeer;

class GetStatementOfAccountTest extends DKBIntegrationTestBase
{
    // Statement request (HKKAZ). Note that DKB's BPD (see InitEndDialogTest) declares only HIKAZSv4 and HIKAZSv5.
    const GET_STATEMENT_REQUEST = "HKKAZ:3:5+1234567890::280:12030000+N+20190901+20190922'";
    const MT940_STATEMENT_1 = "\r\n:20:STARTUMSE\r\n:25:12030000/1234567890\r\n:28C:00000/001\r\n:60F:C190821EUR1234,56\r\n:61:1909030904DR12,00N033NONREF\r\n:86:177?00ONLINE-UEBERWEISUNG?109310?20KREF+HKCCS12345?21SVWZ+323\r\n01000-P111111-33333?22333?23DATUM 02.09.2019, 22.19 UHR?241.TAN 0\r\n12345?30DEUTDEBBXXX?31DExx123412341234123431?32EMPFAENGER ABCDE?3\r\n4997\r\n:62F:C190903EUR1222,56\r\n-\r\n:20:STARTUMSE\r\n:25:12030000/1234567890\r\n:28C:00000/001\r\n:60F:C190903EUR1222,56";
    // NOTE: This contains an 'ä' in UTF-8, but in practice DKB sends it as ISO-8859-1. We cannot hard-code non-UTF8
    // characters here, so utf8_decode() still needs to be called on this before using it.
    const MT940_STATEMENT_2 = "\r\n:61:1909130914CR123,45N060NONREF\r\n:86:152?00GUTSCHR. UEBERW. DAUERAUFTR?109253?20SVWZ+Irgendein Kä\r\nse?30DAAEDEDD?31DExx123412341234123417?32Sender Name1\r\n:62F:C190913EUR1345,01\r\n-";
    const GET_STATEMENT_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+0020::Der Auftrag wurde ausgefuhrt.+0020::Die gebuchten Umsatze wurden ubermittelt.'HIRMS:5:2:4+3076::Starke Kundenauthentifizierung nicht notwendig.'HITAN:6:6:4+4++noref+nochallenge+++SomePhone1'HIKAZ:7:5:3+";

    // Sometimes, DKB wants a TAN.
    const GET_STATEMENT_RESPONSE_BEFORE_TAN = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:4+0030::Auftrag empfangen - Bitte die empfangene TAN eingeben.(MBT65432100002)'HITAN:5:6:4+4++4567-11-30-17.17.09.654321+Bitte geben Sie die pushTAN ein.+++SomePhone1'";
    const SEND_TAN_REQUEST = "HNHBK:1:3+000000000425+300+FAKEDIALOGIDabcdefghijklmnopqr+3'HNVSK:998:3+PIN:2+998+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1:20190102:030405+2:2:13:@8@00000000:5:1+280:12030000:test?@user:V:0:0+0'HNVSD:999:1+@206@HNSHK:2:4+PIN:2+921+9999999+1+1+1::FAKEKUNDENSYSTEMIDabcdefghij+1+1:20190102:030405+1:999:1+6:10:19+280:12030000:test?@user:S:0:0'HKTAN:3:6+2++++4567-11-30-17.17.09.654321+N'HNSHA:4:2+9999999++12345:777666''HNHBS:5:1+3'";
    const SEND_TAN_RESPONSE = "HIRMG:3:2+0010::Nachricht entgegengenommen.'HIRMS:4:2:3+0020::Der Auftrag wurde ausgefuhrt.+0020::Die gebuchten Umsatze wurden ubermittelt.'HITAN:5:6:3+2++4567-11-30-17.17.09.654321'HIKAZ:6:5:3+";

    // Sometimes it paginates.
    const GET_STATEMENT_PAGE_1_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+0020::Der Auftrag wurde ausgefuhrt.+0020::Die gebuchten Umsatze wurden ubermittelt.+3040::Es liegen weitere Informationen vor.:9978-12-08-12.12.22.?:9789'HIRMS:5:2:4+3076::Starke Kundenauthentifizierung nicht notwendig.'HITAN:6:6:4+4++noref+nochallenge+++SomePhone1'HIKAZ:7:5:3+";
    const GET_STATEMENT_PAGE_2_REQUEST = "HKKAZ:3:5+1234567890::280:12030000+N+20190901+20190922++9978-12-08-12.12.22.?:9789'";
    const GET_STATEMENT_PAGE_2_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+0020::Der Auftrag wurde ausgefuhrt.+0020::Die gebuchten Umsatze wurden ubermittelt.'HIRMS:5:2:4+3076::Starke Kundenauthentifizierung nicht notwendig.'HITAN:6:6:4+4++noref+nochallenge+++SomePhone1'HIKAZ:7:5:3+";

    private static function getDkbEncodedBin(string $dataAsUtf8): string
    {
        $content = utf8_decode($dataAsUtf8);
        return '@' . strlen($content) . '@' . $content;
    }

    private static function getHikazContent(): string
    {
        return static::getDkbEncodedBin(self::MT940_STATEMENT_1 . self::MT940_STATEMENT_2);
    }

    private static function getHikazContentPage1(): string
    {
        return static::getDkbEncodedBin(self::MT940_STATEMENT_1);
    }

    private static function getHikazContentPage2(): string
    {
        return static::getDkbEncodedBin(self::MT940_STATEMENT_2);
    }

    /**
     * @throws \Throwable
     */
    private function runInitialRequest(): GetStatementOfAccount
    {
        $getStatement = GetStatementOfAccount::create($this->getTestAccount(),
            new \DateTime('2019-09-01'), new \DateTime('2019-09-22'), false);
        $this->fints->execute($getStatement);
        $getStatement->maybeThrowError();
        return $getStatement;
    }

    /**
     * @throws \Throwable
     */
    public function test_simple()
    {
        $this->initDialog();
        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_RESPONSE . static::getHikazContent() . "'");
        $getStatement = $this->runInitialRequest();
        $this->assertFalse($getStatement->needsTan());
        $this->checkResult($getStatement->getStatement());
    }

    /**
     * @throws \Throwable
     */
    private function completeWithTan(GetStatementOfAccount $getStatement)
    {
        $this->expectMessage(static::SEND_TAN_REQUEST, static::SEND_TAN_RESPONSE . static::getHikazContent() . "'");
        $this->fints->submitTan($getStatement, '777666');
        $this->assertFalse($getStatement->needsTan());
        $this->checkResult($getStatement->getStatement());
    }

    /**
     * @throws \Throwable
     */
    public function test_withTan()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_RESPONSE_BEFORE_TAN);
        $getStatement = $this->runInitialRequest();
        $this->assertTrue($getStatement->needsTan());
        $this->completeWithTan($getStatement);
    }

    /**
     * @throws \Throwable
     */
    public function test_withTan_persist()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_RESPONSE_BEFORE_TAN);
        $getStatement = $this->runInitialRequest();
        $this->assertTrue($getStatement->needsTan());

        // Pretend that we close everything and open everything from scratch, as if it were a new PHP session.
        $persistedInstance = $this->fints->persist();
        $persistedGetStatement = serialize($getStatement);
        $this->connection->expects($this->once())->method('disconnect');
        $this->fints = new FinTsPeer($this->options, $this->credentials, $persistedInstance);
        $this->fints->mockConnection = $this->setUpConnection();
        /** @var GetStatementOfAccount $getStatement */
        $getStatement = unserialize($persistedGetStatement);

        $this->completeWithTan($getStatement);
    }

    /**
     * @throws \Throwable
     */
    public function test_paginated()
    {
        $this->initDialog();
        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_PAGE_1_RESPONSE . static::getHikazContentPage1() . "'");
        $this->expectMessage(static::GET_STATEMENT_PAGE_2_REQUEST, static::GET_STATEMENT_PAGE_2_RESPONSE . static::getHikazContentPage2() . "'");
        $getStatement = $this->runInitialRequest();
        $this->assertFalse($getStatement->needsTan());
        $this->checkResult($getStatement->getStatement());
    }

    /**
     * @throws \Exception
     */
    private function checkResult(StatementOfAccount $statement)
    {
        $this->assertCount(2, $statement->getStatements());

        $statement1 = $statement->getStatements()[0];
        $this->assertEquals(new \DateTime('2019-08-21'), $statement1->getDate());
        $this->assertEquals(Statement::CD_CREDIT, $statement1->getCreditDebit());
        $this->assertEquals(1234.56, $statement1->getStartBalance());
        $this->assertCount(1, $statement1->getTransactions());
        $transaction1 = $statement1->getTransactions()[0];
        $this->assertEquals(new \DateTime('2019-09-03'), $transaction1->getValutaDate());
        $this->assertEquals(new \DateTime('2019-09-04'), $transaction1->getBookingDate());
        $this->assertEquals(Statement::CD_DEBIT, $transaction1->getCreditDebit());
        $this->assertEquals(12.00, $transaction1->getAmount());
        $this->assertEquals('32301000-P111111-33333333 DATUM 02.09.2019, 22.19 UHR1.TAN 012345', $transaction1->getMainDescription());
        $this->assertEquals('HKCCS12345', $transaction1->getStructuredDescription()['KREF']);
        $this->assertEquals('EMPFAENGER ABCDE', $transaction1->getName());

        $statement2 = $statement->getStatements()[1];
        $this->assertEquals(new \DateTime('2019-09-03'), $statement2->getDate());
        $this->assertEquals(Statement::CD_CREDIT, $statement2->getCreditDebit());
        $this->assertEquals(1234.56 - 12.00, $statement2->getStartBalance());
        $this->assertCount(1, $statement2->getTransactions());
        $transaction2 = $statement2->getTransactions()[0];
        $this->assertEquals(new \DateTime('2019-09-13'), $transaction2->getValutaDate());
        $this->assertEquals(new \DateTime('2019-09-14'), $transaction2->getBookingDate());
        $this->assertEquals(Statement::CD_CREDIT, $transaction2->getCreditDebit());
        $this->assertEquals(123.45, $transaction2->getAmount());
        $this->assertEquals(['SVWZ' => 'Irgendein Käse'], $transaction2->getStructuredDescription());
        $this->assertEquals('Sender Name1', $transaction2->getName());
    }
}
