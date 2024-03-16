<?php

namespace Tests\Fhp\Integration\Consors;

use Fhp\Model\StatementOfAccount\Statement;

class GetStatementOfAccountTest extends ConsorsIntegrationTestBase
{
    // Statement request (HKKAZ).
    public const GET_STATEMENT_REQUEST = "HKKAZ:3:7+DExxABCDEFGH1234567890:CSDBDE71XXX:1234567890::280:50220500+N+20190601+20190922'HKTAN:4:6+4+HKKAZ'";

    // Note: Consorsbank weirdly returns November statements even when only up to September was requested.
    public const GET_STATEMENT_RESPONSE = "HIRMG:2:2:+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+0020::Der Auftrag wurde ausgefuhrt.+3076::Keine starke Authentifizierung erforderlich.+3997::Der Auftrag wurde nur teilweise ausgefuhrt.'"
    . "HIKAZ:4:7:3+@1034@\r\n"
    . ":20:0\r\n:21:NONREF\r\n:25:50220500/123456789\r\n:28C:0/7\r\n:60M:C191118EUR950,59\r\n"
    . ":61:1911181118D2,8N008NONREF\r\n:86:008?00Dauerauftrag?20EREF+NOTPROVIDED           ?21             ?\r\n22KREF+NONREF?23SVWZ+XY?30BICBICBICBI?31DExx444444444444444444?32\r\nMax Mustermannig\r\n:62M:C191118EUR947,79\r\n-\r\n"
    . ":20:0\r\n:21:NONREF\r\n:25:50220500/123456789\r\n:28C:0/8\r\n:60M:C191120EUR947,79\r\n"
    . ":61:1911201120D11,3N005NONREF\r\n:86:005?00Lastschrift (Einzugsermächtigung)?20EREF+ZAA0987654321     \r\n    ?21             ?22KREF+NONREF?23SVWZ+LogPay OnlineTicket i.?\r\n24A.v. Irgendeine Firma und S?25oehne AG. Ihre Kundenn r. 2?26019\r\n999999999?30BICBICBI?31DExx555555555555555555?32LOGPAY FINANCIAL \r\nSERVICES G?33MBH\r\n"
    . ":61:1911201120D15,5N005NONREF\r\n:86:005?00Lastschrift (Einzugsermächtigung)?20EREF+ZAA0123456789     \r\n    ?21             ?22KREF+NONREF?23SVWZ+LogPay OnlineTicket i.?\r\n24A.v. Irgendeine Firma und S?25oehne AG. Ihre Kundenn r. 2?26019\r\n999999999?30BICBICBI?31DExx555555555555555555?32LOGPAY FINANCIAL \r\nSERVICES G?33MBH\r\n"
    . ":62F:C191120EUR920,99\r\n-'"
    . "HITAN:5:6:4+4++noref+nochallenge'";

    // Note: There is no HIKAZ at all in this response, but it's still valid.
    public const GET_STATEMENT_EMPTY_RESPONSE = "HIRMG:2:2:+3060::Teilweise liegen Warnungen/Hinweise vor.'HIRMS:3:2:3+3010::Keine Umsatze gefunden+3076::Keine starke Authentifizierung erforderlich.'HITAN:4:6:4+4++noref+nochallenge'";

    /**
     * @throws \Throwable
     */
    public function testGetStatementOfAccount()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_RESPONSE);
        $getStatement = \Fhp\Action\GetStatementOfAccount::create(
            $this->getTestAccount(),
            new \DateTime('2019-06-01'), new \DateTime('2019-09-22')
        );
        $this->fints->execute($getStatement);
        $statement = $getStatement->getStatement();

        $this->assertCount(2, $statement->getStatements());

        $statement1 = $statement->getStatements()[0];
        $this->assertEquals(new \DateTime('2019-11-18'), $statement1->getDate());
        $this->assertEquals(Statement::CD_CREDIT, $statement1->getCreditDebit());
        $this->assertEqualsWithDelta(950.59, $statement1->getStartBalance(), 0.01);
        $this->assertCount(1, $statement1->getTransactions());
        $transaction1 = $statement1->getTransactions()[0];
        $this->assertEquals(new \DateTime('2019-11-18'), $transaction1->getValutaDate());
        $this->assertEquals(new \DateTime('2019-11-18'), $transaction1->getBookingDate());
        $this->assertEquals(Statement::CD_DEBIT, $transaction1->getCreditDebit());
        $this->assertEqualsWithDelta(2.80, $transaction1->getAmount(), 0.01);
        $this->assertEquals('XY', $transaction1->getMainDescription());
        $this->assertEquals('NONREF', $transaction1->getStructuredDescription()['KREF']);
        $this->assertEquals('Max Mustermannig', $transaction1->getName());

        $statement2 = $statement->getStatements()[1];
        $this->assertEquals(new \DateTime('2019-11-20'), $statement2->getDate());
        $this->assertEquals(Statement::CD_CREDIT, $statement2->getCreditDebit());
        $this->assertEqualsWithDelta(950.59 - 2.80, $statement2->getStartBalance(), 0.01);
        $this->assertCount(2, $statement2->getTransactions());
        $transaction2 = $statement2->getTransactions()[0];
        $this->assertEquals(new \DateTime('2019-11-20'), $transaction2->getValutaDate());
        $this->assertEquals(new \DateTime('2019-11-20'), $transaction2->getBookingDate());
        $this->assertEquals(Statement::CD_DEBIT, $transaction2->getCreditDebit());
        $this->assertEqualsWithDelta(11.30, $transaction2->getAmount(), 0.01);
        $this->assertEquals('Lastschrift (Einzugsermächtigung)', $transaction2->getBookingText());
        $this->assertEquals('ZAA0987654321', $transaction2->getStructuredDescription()['EREF']);
        $this->assertEquals('LOGPAY FINANCIAL SERVICES GMBH', $transaction2->getName());
        $transaction3 = $statement2->getTransactions()[1];
        $this->assertEqualsWithDelta(15.50, $transaction3->getAmount(), 0.01);
        $this->assertEquals('ZAA0123456789', $transaction3->getStructuredDescription()['EREF']);
    }

    /**
     * @throws \Throwable
     */
    public function testGetStatementOfAccountEmpty()
    {
        $this->initDialog();

        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_EMPTY_RESPONSE);
        $getStatement = \Fhp\Action\GetStatementOfAccount::create(
            $this->getTestAccount(),
            new \DateTime('2019-06-01'), new \DateTime('2019-09-22')
        );
        $this->fints->execute($getStatement);
        $statement = $getStatement->getStatement();

        $this->assertEmpty($statement->getStatements());
    }
}
