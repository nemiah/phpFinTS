<?php

namespace Tests\Fhp\Integration\DKB;

use Fhp\Model\SEPAAccount;
use Fhp\Model\StatementOfAccount\Statement;

class GetStatementOfAccountTest extends DKBIntegrationTestBase
{
    // Statement request (HKKAZ). Note that DKB's BPD (see InitEndDialogTest) declares only HIKAZSv4 and HIKAZSv5.
    const GET_STATEMENT_REQUEST = "HKKAZ:3:5+1234567890::280:12030000+N+20190901+20190922'";
    const GET_STATEMENT_RESPONSE = "HIRMG:3:2+3060::Bitte beachten Sie die enthaltenen Warnungen/Hinweise.'HIRMS:4:2:3+0020::Der Auftrag wurde ausgefuhrt.+0020::Die gebuchten Umsatze wurden ubermittelt.'HIRMS:5:2:4+3076::Starke Kundenauthentifizierung nicht notwendig.'HITAN:6:6:4+4++noref+nochallenge+++SomePhone1'HIKAZ:7:5:3+@611@\r\n:20:STARTUMSE\r\n:25:12030000/1234567890\r\n:28C:00000/001\r\n:60F:C190821EUR1234,56\r\n:61:1909030904DR12,00N033NONREF\r\n:86:177?00ONLINE-UEBERWEISUNG?109310?20KREF+HKCCS12345?21SVWZ+323\r\n01000-P111111-33333?22333?23DATUM 02.09.2019, 22.19 UHR?241.TAN 0\r\n12345?30DEUTDEBBXXX?31DExx123412341234123431?32EMPFAENGER ABCDE?3\r\n4997\r\n:62F:C190903EUR1222,56\r\n-\r\n:20:STARTUMSE\r\n:25:12030000/1234567890\r\n:28C:00000/001\r\n:60F:C190903EUR1222,56\r\n:61:1909130914CR123,45N060NONREF\r\n:86:152?00GUTSCHR. UEBERW. DAUERAUFTR?109253?20SVWZ+Irgendein Te\r\nxttt?30DAAEDEDD?31DExx123412341234123417?32Sender Name1\r\n:62F:C190913EUR1345,01\r\n-'";

    /**
     * @throws \Throwable
     */
    public function test_getStatementOfAccount()
    {
        $this->initDialog();

        $sepaAccount = new SEPAAccount();
        $sepaAccount->setIban('DExxABCDEFGH1234567890');
        $sepaAccount->setBic('BYLADEM1001');
        $sepaAccount->setAccountNumber('1234567890');
        $sepaAccount->setBlz('12030000');

        $this->expectMessage(static::GET_STATEMENT_REQUEST, static::GET_STATEMENT_RESPONSE);
        $getStatement = \Fhp\Action\GetStatementOfAccount::create($sepaAccount, new \DateTime('2019-09-01'), new \DateTime('2019-09-22'), false);
        $this->fints->execute($getStatement);
        $statement = $getStatement->getStatement();

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
        $this->assertEquals(['SVWZ' => 'Irgendein Texttt'], $transaction2->getStructuredDescription());
        $this->assertEquals('Sender Name1', $transaction2->getName());
    }
}
